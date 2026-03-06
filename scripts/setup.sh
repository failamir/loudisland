#!/bin/bash
# =============================================================
#  LOUDISLAND — FULL SETUP SCRIPT (FROM SCRATCH)
#  Proposed Scalable Real-Time Backend Architecture
#  Author: Fail Amir Abdullah, M.Kom.
#  Usage: chmod +x scripts/setup.sh && ./scripts/setup.sh
# =============================================================

set -e

# ─────────────────────────────────────────────
# Colors
# ─────────────────────────────────────────────
RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
BLUE='\033[0;34m'; CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

info()    { echo -e "${CYAN}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }
step()    { echo -e "\n${BOLD}${BLUE}━━━━ STEP $1 ━━━━${NC}"; }

# ─────────────────────────────────────────────
# Banner
# ─────────────────────────────────────────────
echo -e "${BOLD}${CYAN}"
echo "╔══════════════════════════════════════════════════════════╗"
echo "║    LOUDISLAND — SCALABLE REAL-TIME BACKEND SETUP        ║"
echo "║    Nginx LB → 4x PHP → Redis + RabbitMQ → MySQL         ║"
echo "║    Author: Fail Amir Abdullah, M.Kom.                   ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo -e "${NC}"

# ─────────────────────────────────────────────
# STEP 1: Check Prerequisites
# ─────────────────────────────────────────────
step "1 — Checking Prerequisites"

check_cmd() {
    if ! command -v "$1" &>/dev/null; then
        error "$1 is not installed. Please install it first."
    fi
    success "$1 found"
}

check_cmd docker
check_cmd docker-compose

DOCKER_VERSION=$(docker --version | awk '{print $3}' | tr -d ',')
info "Docker version: $DOCKER_VERSION"

# ─────────────────────────────────────────────
# STEP 2: Environment File
# ─────────────────────────────────────────────
step "2 — Setting Up Environment"

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

if [ ! -f ".env" ]; then
    if [ -f ".env.example" ]; then
        cp .env.example .env
        success "Copied .env.example → .env"
    else
        error ".env.example not found!"
    fi
else
    warn ".env already exists — skipping copy"
fi

# Prompt for sensitive values
echo ""
echo -e "${YELLOW}Configure environment (press ENTER to keep defaults):${NC}"

read -p "DB Database name [loudisland]: " db_name
read -p "DB Username [loudisland]: "       db_user
read -s -p "DB Password [secret]: "        db_pass
echo ""
read -s -p "DB Root Password [secret]: "   db_root
echo ""
read -p "RabbitMQ Username [admin]: "      rmq_user
read -s -p "RabbitMQ Password [secret]: "  rmq_pass
echo ""

db_name=${db_name:-loudisland}
db_user=${db_user:-loudisland}
db_pass=${db_pass:-secret}
db_root=${db_root:-secret}
rmq_user=${rmq_user:-admin}
rmq_pass=${rmq_pass:-secret}

# Update .env
update_env() {
    local key="$1" value="$2"
    if grep -q "^${key}=" .env; then
        sed -i.bak "s|^${key}=.*|${key}=${value}|" .env
    else
        echo "${key}=${value}" >> .env
    fi
}

update_env "DB_DATABASE"        "$db_name"
update_env "DB_USERNAME"        "$db_user"
update_env "DB_PASSWORD"        "$db_pass"
update_env "DB_ROOT_PASSWORD"   "$db_root"
update_env "DB_HOST"            "mysql-master"
update_env "DB_WRITE_HOST"      "mysql-master"
update_env "DB_READ_HOST"       "mysql-slave"
update_env "REDIS_HOST"         "redis"
update_env "RABBITMQ_HOST"      "rabbitmq"
update_env "RABBITMQ_USER"      "$rmq_user"
update_env "RABBITMQ_PASSWORD"  "$rmq_pass"
update_env "RABBITMQ_VHOST"     "$db_name"
update_env "BROADCAST_DRIVER"   "redis"
update_env "CACHE_DRIVER"       "redis"
update_env "SESSION_DRIVER"     "redis"
update_env "QUEUE_CONNECTION"   "rabbitmq"

success "Environment variables updated"

# ─────────────────────────────────────────────
# STEP 3: Build Docker Images
# ─────────────────────────────────────────────
step "3 — Building Docker Images"

info "Building PHP 8.1 app image..."
docker-compose build --no-cache app1
success "PHP app image built"

# ─────────────────────────────────────────────
# STEP 4: Start Infrastructure Services First
# ─────────────────────────────────────────────
step "4 — Starting Infrastructure Services (MySQL, Redis, RabbitMQ)"

docker-compose up -d mysql-master redis rabbitmq
info "Waiting for MySQL master to be ready (30s)..."
sleep 30

# Wait for MySQL to accept connections
RETRIES=15
until docker-compose exec -T mysql-master mysqladmin ping -u root -p"$db_root" --silent; do
    RETRIES=$((RETRIES - 1))
    [ $RETRIES -le 0 ] && error "MySQL master failed to start!"
    info "MySQL not ready yet... retrying ($RETRIES left)"
    sleep 5
done
success "MySQL master is ready"

# Wait for Redis
until docker-compose exec -T redis redis-cli ping | grep -q PONG; do
    info "Redis not ready... retrying"
    sleep 3
done
success "Redis is ready"

# Wait for RabbitMQ
info "Waiting for RabbitMQ to be ready (20s)..."
sleep 20
success "RabbitMQ started (management UI at http://localhost:15672)"

# ─────────────────────────────────────────────
# STEP 5: Setup MySQL Replication
# ─────────────────────────────────────────────
step "5 — Setting Up MySQL Master→Slave Replication"

# Get master binlog position
MASTER_LOG_FILE=$(docker-compose exec -T mysql-master mysql -u root -p"$db_root" -e "SHOW MASTER STATUS\G" 2>/dev/null | grep "File:" | awk '{print $2}')
MASTER_LOG_POS=$(docker-compose exec -T mysql-master mysql -u root -p"$db_root" -e "SHOW MASTER STATUS\G" 2>/dev/null | grep "Position:" | awk '{print $2}')

if [ -z "$MASTER_LOG_FILE" ]; then
    warn "Could not detect master log file. Replication setup will be attempted manually."
    MASTER_LOG_FILE="mysql-bin.000001"
    MASTER_LOG_POS=4
fi

info "Master Log File: $MASTER_LOG_FILE @ Position: $MASTER_LOG_POS"

# Start slave container
docker-compose up -d mysql-slave
info "Waiting for MySQL slave to start (20s)..."
sleep 20

# Configure slave to replicate from master
docker-compose exec -T mysql-slave mysql -u root -p"$db_root" <<-EOSQL
    STOP SLAVE;
    RESET SLAVE ALL;
    CHANGE MASTER TO
        MASTER_HOST='mysql-master',
        MASTER_USER='replicator',
        MASTER_PASSWORD='replicator_secret_2024',
        MASTER_LOG_FILE='${MASTER_LOG_FILE}',
        MASTER_LOG_POS=${MASTER_LOG_POS};
    START SLAVE;
EOSQL

# Verify replication
sleep 5
SLAVE_STATUS=$(docker-compose exec -T mysql-slave mysql -u root -p"$db_root" -e "SHOW SLAVE STATUS\G" 2>/dev/null)
if echo "$SLAVE_STATUS" | grep -q "Slave_IO_Running: Yes"; then
    success "MySQL replication is running (Slave_IO_Running: Yes)"
else
    warn "Replication may not be running. Check: docker-compose exec mysql-slave mysql -u root -p'$db_root' -e 'SHOW SLAVE STATUS\\G'"
fi

# ─────────────────────────────────────────────
# STEP 6: Start All App Containers
# ─────────────────────────────────────────────
step "6 — Starting App Containers (4x PHP-FPM) + Nginx + Worker"

docker-compose up -d
success "All containers started"

# ─────────────────────────────────────────────
# STEP 7: Laravel Application Setup
# ─────────────────────────────────────────────
step "7 — Laravel Application Setup"

info "Generating application key..."
docker-compose exec -T app1 php artisan key:generate --force
success "App key generated"

info "Running database migrations..."
docker-compose exec -T app1 php artisan migrate --force
success "Migrations completed"

info "Clearing and caching config..."
docker-compose exec -T app1 php artisan config:cache
docker-compose exec -T app1 php artisan route:cache
docker-compose exec -T app1 php artisan view:cache
success "Laravel cache optimized"

info "Creating storage symlink..."
docker-compose exec -T app1 php artisan storage:link || true
success "Storage link created"

# ─────────────────────────────────────────────
# STEP 8: Fix Permissions
# ─────────────────────────────────────────────
step "8 — Fixing File Permissions"

for svc in app1 app2 app3 app4 worker; do
    docker-compose exec -T "$svc" chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    docker-compose exec -T "$svc" chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
done
success "Permissions set on all containers"

# ─────────────────────────────────────────────
# STEP 9: Health Check
# ─────────────────────────────────────────────
step "9 — Running Health Checks"

bash "$(dirname "$0")/health-check.sh"

# ─────────────────────────────────────────────
# Done!
# ─────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}"
echo "╔══════════════════════════════════════════════════════════╗"
echo "║  ✅  SETUP COMPLETE!                                     ║"
echo "╠══════════════════════════════════════════════════════════╣"
echo "║  🌐  App URL        → http://localhost                   ║"
echo "║  🐰  RabbitMQ UI    → http://localhost:15672             ║"
echo "║  🗄️   MySQL Master   → localhost:3306                    ║"
echo "║  🗄️   MySQL Slave    → localhost:3307                    ║"
echo "║  ⚡  Redis          → localhost:6379                     ║"
echo "╚══════════════════════════════════════════════════════════╝"
echo -e "${NC}"
echo -e "  Scale up:   ${CYAN}./scripts/scale.sh up 6${NC}"
echo -e "  Deploy:     ${CYAN}./scripts/deploy.sh${NC}"
echo -e "  Health:     ${CYAN}./scripts/health-check.sh${NC}"
echo ""

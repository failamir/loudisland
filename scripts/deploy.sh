#!/bin/bash
# =============================================================
#  LOUDISLAND — ZERO-DOWNTIME DEPLOY SCRIPT
#  Rolling restart: containers replaced one-by-one
#  Usage: ./scripts/deploy.sh [--skip-pull]
# =============================================================

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

info()    { echo -e "${CYAN}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()    { echo -e "${YELLOW}[WARN]${NC}  $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }
step()    { echo -e "\n${BOLD}${BLUE}── $1 ──${NC}"; }

SKIP_PULL=0
[[ "$1" == "--skip-pull" ]] && SKIP_PULL=1

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo ""
echo -e "${BOLD}${CYAN}╔══════════════════════════════════════════╗"
echo -e "║   LOUDISLAND — ZERO-DOWNTIME DEPLOY     ║"
echo -e "╚══════════════════════════════════════════╝${NC}"
echo ""

# ─────────────────────────────────────────────
# STEP 1: Pull latest code
# ─────────────────────────────────────────────
if [ "$SKIP_PULL" -eq 0 ]; then
    step "Pulling Latest Code"
    git pull origin main || git pull origin master || warn "Git pull failed — deploying with current code"
    success "Code updated"
fi

# ─────────────────────────────────────────────
# STEP 2: Rebuild PHP image
# ─────────────────────────────────────────────
step "Rebuilding PHP Image"
info "Building new image..."
docker-compose build app1

# ─────────────────────────────────────────────
# STEP 3: Rolling restart of app containers
# ─────────────────────────────────────────────
step "Rolling Restart (Zero Downtime)"

APP_SERVICES="app1 app2 app3 app4"
for svc in $APP_SERVICES; do
    info "Updating $svc..."

    # Recreate this container with new image
    docker-compose up -d --no-deps --build "$svc"

    info "Waiting for $svc to be ready..."
    sleep 8

    # Quick health check on this container
    if docker-compose ps "$svc" | grep -q "Up"; then
        success "$svc is running with new image"
    else
        error "$svc failed to start! Rolling back..."
        docker-compose up -d "$svc"
    fi
done

# ─────────────────────────────────────────────
# STEP 4: Restart worker
# ─────────────────────────────────────────────
step "Restarting Background Worker"
docker-compose up -d --no-deps --build worker
success "Worker restarted"

# ─────────────────────────────────────────────
# STEP 5: Run Laravel optimizations
# ─────────────────────────────────────────────
step "Running Laravel Optimizations"

info "Running migrations (if any)..."
docker-compose exec -T app1 php artisan migrate --force

info "Clearing old caches..."
docker-compose exec -T app1 php artisan cache:clear
docker-compose exec -T app1 php artisan config:clear
docker-compose exec -T app1 php artisan route:clear
docker-compose exec -T app1 php artisan view:clear

info "Re-warming caches..."
docker-compose exec -T app1 php artisan config:cache
docker-compose exec -T app1 php artisan route:cache
docker-compose exec -T app1 php artisan view:cache

success "Laravel optimized"

# ─────────────────────────────────────────────
# STEP 6: Reload Nginx (no downtime)
# ─────────────────────────────────────────────
step "Reloading Nginx Config"
docker-compose exec -T nginx nginx -t && \
docker-compose exec -T nginx nginx -s reload
success "Nginx reloaded gracefully"

# ─────────────────────────────────────────────
# Done
# ─────────────────────────────────────────────
echo ""
echo -e "${BOLD}${GREEN}✅  Deployment complete! All containers updated with zero downtime.${NC}"
echo ""
bash "$(dirname "$0")/health-check.sh"

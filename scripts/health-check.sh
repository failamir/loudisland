#!/bin/bash
# =============================================================
#  LOUDISLAND — HEALTH CHECK SCRIPT
#  Checks status of all services in the scalable architecture
#  Usage: ./scripts/health-check.sh
# =============================================================

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

ok()   { echo -e "  ${GREEN}✅  $1${NC}"; }
fail() { echo -e "  ${RED}❌  $1${NC}"; FAILED=$((FAILED+1)); }
warn() { echo -e "  ${YELLOW}⚠️   $1${NC}"; }

FAILED=0

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

echo ""
echo -e "${BOLD}${CYAN}═══ LOUDISLAND — SERVICE HEALTH CHECK ═══${NC}"
echo ""

# ─── Nginx ───
echo -e "${BOLD}[1] Nginx Load Balancer${NC}"
if docker-compose ps nginx 2>/dev/null | grep -q "Up"; then
    ok "nginx container is UP"
    if curl -sf --max-time 5 http://localhost > /dev/null 2>&1; then
        ok "HTTP accessible at http://localhost"
    else
        warn "Cannot reach http://localhost (may still be starting)"
    fi
else
    fail "nginx container is DOWN"
fi

# ─── PHP App Containers ───
echo -e "\n${BOLD}[2] PHP App Containers (Load Balanced)${NC}"
for svc in app1 app2 app3 app4; do
    if docker-compose ps "$svc" 2>/dev/null | grep -q "Up"; then
        ok "$svc is UP"
    else
        fail "$svc is DOWN"
    fi
done

# ─── Worker ───
echo -e "\n${BOLD}[3] Background Worker Service${NC}"
if docker-compose ps worker 2>/dev/null | grep -q "Up"; then
    ok "worker is UP (consuming queue)"
else
    fail "worker is DOWN"
fi

# ─── MySQL Master ───
echo -e "\n${BOLD}[4] MySQL Master (Write Node)${NC}"
if docker-compose exec -T mysql-master mysqladmin ping --silent 2>/dev/null; then
    ok "mysql-master is UP and accepting connections"
else
    fail "mysql-master is DOWN or not responding"
fi

# ─── MySQL Slave ───
echo -e "\n${BOLD}[5] MySQL Slave (Read Replica)${NC}"
if docker-compose ps mysql-slave 2>/dev/null | grep -q "Up"; then
    ok "mysql-slave container is UP"
    SLAVE_IO=$(docker-compose exec -T mysql-slave mysql -u root -p"${DB_ROOT_PASSWORD:-secret}" -se "SHOW SLAVE STATUS\G" 2>/dev/null | grep "Slave_IO_Running:" | awk '{print $2}')
    SLAVE_SQL=$(docker-compose exec -T mysql-slave mysql -u root -p"${DB_ROOT_PASSWORD:-secret}" -se "SHOW SLAVE STATUS\G" 2>/dev/null | grep "Slave_SQL_Running:" | awk '{print $2}')
    if [ "$SLAVE_IO" = "Yes" ] && [ "$SLAVE_SQL" = "Yes" ]; then
        ok "Replication running (IO: $SLAVE_IO, SQL: $SLAVE_SQL)"
    else
        warn "Replication may be delayed (IO: ${SLAVE_IO:-?}, SQL: ${SLAVE_SQL:-?})"
    fi
else
    fail "mysql-slave is DOWN"
fi

# ─── Redis ───
echo -e "\n${BOLD}[6] Redis (Cache + Session + Pub/Sub)${NC}"
if docker-compose exec -T redis redis-cli ping 2>/dev/null | grep -q PONG; then
    ok "redis is UP and responding to PING"
    REDIS_MEMORY=$(docker-compose exec -T redis redis-cli info memory 2>/dev/null | grep "used_memory_human:" | awk -F: '{print $2}' | tr -d '\r')
    REDIS_CLIENTS=$(docker-compose exec -T redis redis-cli info clients 2>/dev/null | grep "connected_clients:" | awk -F: '{print $2}' | tr -d '\r')
    ok "Memory used: ${REDIS_MEMORY:-N/A} | Connected clients: ${REDIS_CLIENTS:-N/A}"
else
    fail "redis is DOWN"
fi

# ─── RabbitMQ ───
echo -e "\n${BOLD}[7] RabbitMQ (Async Task Queue)${NC}"
if docker-compose exec -T rabbitmq rabbitmq-diagnostics ping 2>/dev/null | grep -q "Ping"; then
    ok "rabbitmq is UP"
    ok "Management UI available at http://localhost:15672"
else
    if docker-compose ps rabbitmq 2>/dev/null | grep -q "Up"; then
        warn "rabbitmq container is UP but diagnostics timed out (may still be starting)"
    else
        fail "rabbitmq is DOWN"
    fi
fi

# ─── Summary ───
echo ""
echo -e "${BOLD}═══ SUMMARY ═══${NC}"
TOTAL=7
PASSED=$((TOTAL - FAILED))
if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}${BOLD}All services are healthy! ($PASSED/$TOTAL)${NC}"
else
    echo -e "${YELLOW}${BOLD}$PASSED/$TOTAL services OK — $FAILED issue(s) detected${NC}"
    echo -e "${YELLOW}Run: docker-compose logs <service-name> to debug${NC}"
fi
echo ""

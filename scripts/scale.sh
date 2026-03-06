#!/bin/bash
# =============================================================
#  LOUDISLAND — SCALE SCRIPT
#  Scale PHP app containers up or down
#  Usage:
#    ./scripts/scale.sh up 6     → scale to 6 containers
#    ./scripts/scale.sh down 2   → scale down to 2 containers
#    ./scripts/scale.sh status   → show current scale
# =============================================================

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'
CYAN='\033[0;36m'; BOLD='\033[1m'; NC='\033[0m'

info()    { echo -e "${CYAN}[INFO]${NC}  $1"; }
success() { echo -e "${GREEN}[OK]${NC}    $1"; }
error()   { echo -e "${RED}[ERROR]${NC} $1"; exit 1; }

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$PROJECT_ROOT"

ACTION="${1:-status}"
COUNT="${2:-4}"

case "$ACTION" in
    up|UP)
        if [ -z "$COUNT" ]; then
            error "Usage: ./scripts/scale.sh up <count>"
        fi
        info "Scaling PHP app containers to $COUNT replicas..."
        docker-compose up -d --scale app1=1 --scale app2=1 --scale app3=1 --scale app4=1

        # If more than 4, spin additional containers using docker-compose scale
        if [ "$COUNT" -gt 4 ]; then
            EXTRA=$((COUNT - 4))
            info "Spinning $EXTRA additional containers..."
            docker-compose up -d --scale app4=$((1 + EXTRA))
        fi

        success "Scaled to $COUNT containers"
        info "Nginx will automatically load balance to new containers"
        info "Rebuild Nginx config if needed:"
        echo -e "  ${CYAN}docker-compose exec nginx nginx -s reload${NC}"
        ;;

    down|DOWN)
        if [ -z "$COUNT" ] || [ "$COUNT" -lt 1 ]; then
            error "Usage: ./scripts/scale.sh down <count> (minimum: 1)"
        fi
        if [ "$COUNT" -lt 4 ]; then
            warn "Scaling below 4 containers — some upstream will be unused"
        fi
        info "Scaling down to $COUNT containers..."

        # Remove extra containers gracefully
        RUNNING=$(docker-compose ps -q app1 app2 app3 app4 worker | wc -l | tr -d ' ')
        if [ "$COUNT" -ge "$RUNNING" ]; then
            warn "Already at $RUNNING containers, no scale-down needed"
            exit 0
        fi

        # Stop the higher-numbered containers first
        for svc in app4 app3 app2 app1; do
            CURRENT=$(docker-compose ps -q "$svc" 2>/dev/null | wc -l | tr -d ' ')
            if [ "$CURRENT" -gt 0 ] && [ "$RUNNING" -gt "$COUNT" ]; then
                info "Gracefully stopping $svc..."
                docker-compose stop "$svc" --timeout 30
                RUNNING=$((RUNNING - 1))
            fi
        done
        success "Scaled down to $COUNT containers"
        ;;

    status|STATUS)
        echo -e "\n${BOLD}${CYAN}═══ CURRENT CONTAINER STATUS ═══${NC}"
        docker-compose ps
        echo ""
        RUNNING=$(docker-compose ps -q app1 app2 app3 app4 2>/dev/null | wc -l | tr -d ' ')
        echo -e "  PHP App containers running: ${BOLD}${GREEN}$RUNNING${NC}"
        echo ""
        ;;

    *)
        echo "Usage: ./scripts/scale.sh <up|down|status> [count]"
        echo "  up <N>      Scale to N app containers"
        echo "  down <N>    Scale down to N app containers"
        echo "  status      Show current running containers"
        exit 1
        ;;
esac

#!/usr/bin/env bash
set -euo pipefail

# =============================================================================
# filmes100ads — Local Deploy Script
#
# Reads FTP credentials from .env.ftp, builds vendor.zip,
# uploads everything via lftp, and prints the setup URL.
#
# Usage:
#   ./scripts/deploy.sh [--vendor-only] [--reset|--reset-auto]
#
# Flags:
#   --vendor-only     Generate and upload ONLY vendor.zip (no app code)
#   --reset           Upload reset script + PAUSE for browser visit + full deploy
#   --reset-auto      Upload reset script + full deploy (no pause)
# =============================================================================

VENDOR_ONLY=false
RESET=false
RESET_AUTO=false

for arg in "$@"; do
    case "$arg" in
        --vendor-only) VENDOR_ONLY=true ;;
        --reset) RESET=true ;;
        --reset-auto) RESET_AUTO=true; RESET=true ;;
    esac
done

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# --- Colors ---
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
CYAN='\033[0;36m'
NC='\033[0m'

info()  { echo -e "${CYAN}▸${NC} $*"; }
ok()    { echo -e "${GREEN}✔${NC} $*"; }
warn()  { echo -e "${YELLOW}⚠${NC} $*"; }
fail()  { echo -e "${RED}✘${NC} $*"; exit 1; }

# --- Load .env.ftp ---
ENV_FTP="$PROJECT_DIR/.env.ftp"
if [[ ! -f "$ENV_FTP" ]]; then
    fail ".env.ftp not found at $ENV_FTP"
fi

while IFS='=' read -r key value; do
    key=$(echo "$key" | xargs)
    value=$(echo "$value" | tr -d "'\"")
    case "$key" in
        INFINITY_FREE_HOST)     FTP_HOST="$value" ;;
        INFINITY_FREE_USER)     FTP_USER="$value" ;;
        INFINITY_FREE_PASSWORD) FTP_PASS="$value" ;;
        INFINITY_FREE_PATH)     FTP_PATH="$value" ;;
    esac
done < "$ENV_FTP"

# --- Load DEPLOY_KEY from .env or .env.ftp ---
DEPLOY_KEY=""
ENV_FILE="$PROJECT_DIR/.env"
if [[ -f "$ENV_FILE" ]]; then
    DEPLOY_KEY=$(grep "^DEPLOY_KEY=" "$ENV_FILE" | head -1 | cut -d= -f2 | tr -d "\"'" || true)
fi
if [[ -z "$DEPLOY_KEY" ]] && [[ -f "$ENV_FTP" ]]; then
    DEPLOY_KEY=$(grep "^DEPLOY_KEY=" "$ENV_FTP" | head -1 | cut -d= -f2 | tr -d "\"'" || true)
fi
if [[ -z "$DEPLOY_KEY" ]]; then
    fail "DEPLOY_KEY not found in .env or .env.ftp"
fi

# --- Determine target paths ---
# P4O pattern: Everything lives inside htdocs/
#   /filmes100ads.free.nf/htdocs/ = Laravel root (app, config, vendor, .env, etc.)
#   /filmes100ads.free.nf/htdocs/public/ = Laravel public dir (index.php, build/)
HTDOCS_PATH="/filmes100ads.free.nf/htdocs/"
SETUP_DOMAIN="http://filmes100ads.free.nf"

# --- Validate ---
if [ -z "${FTP_HOST:-}" ] || [ -z "${FTP_USER:-}" ] || [ -z "${FTP_PASS:-}" ]; then
    fail "FTP credentials incomplete in .env.ftp"
fi

echo ""
echo -e "${CYAN}============================================${NC}"
if [ "$RESET" = true ] && [ "$VENDOR_ONLY" = true ]; then
    echo -e "${CYAN}  filmes100ads — Reset + Deploy (vendor-only)${NC}"
elif [ "$RESET" = true ]; then
    echo -e "${CYAN}  filmes100ads — Reset + Deploy Full${NC}"
elif [ "$VENDOR_ONLY" = true ]; then
    echo -e "${CYAN}  filmes100ads — Deploy (vendor-only)${NC}"
else
    echo -e "${CYAN}  filmes100ads — Deploy Full${NC}"
fi
echo -e "${CYAN}============================================${NC}"
echo ""
info "Host:   $FTP_HOST"
info "Htdocs: $HTDOCS_PATH"
info "Mode:   $([ "$VENDOR_ONLY" = true ] && echo 'vendor-only' || echo 'full')"
echo ""

# --- Reset phase ---
if [ "$RESET" = true ]; then
    info "Reset: uploading server-reset.php..."

    lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<LFTP_RESET
set ftp:ssl-allow no
set net:timeout 30
set net:max-retries 3
set net:reconnect-interval-base 5
put public/server-reset.php -o ${HTDOCS_PATH}/server-reset.php
bye
LFTP_RESET

    ok "server-reset.php uploaded"

    RESET_URL="${SETUP_DOMAIN}/server-reset.php?key=${DEPLOY_KEY}"
    echo ""
    echo -e "  ${YELLOW}Visit this URL in your browser to clear all caches on the server:${NC}"
    echo -e "  ${YELLOW}(The browser resolves the InfinityFree anti-bot challenge automatically)${NC}"
    echo ""
    echo -e "  ${CYAN}${RESET_URL}${NC}"
    echo ""

    if [ "$RESET_AUTO" = false ]; then
        echo -e "  ${YELLOW}Press Enter after visiting the URL to continue deploy...${NC}"
        read -r
    else
        echo -e "  ${YELLOW}Continuing automatically...${NC}"
        echo ""
    fi
fi

# --- Step 1: Composer install ---
info "Step 1: Installing production dependencies..."
cd "$PROJECT_DIR"
composer install --no-dev --optimize-autoloader --no-interaction --quiet
ok "Dependencies installed"

# --- Step 2: Vite build ---
info "Step 2: Building Vite assets..."
npm run build --prefix "$PROJECT_DIR"
ok "Vite build complete"

# --- Step 3: Create vendor.zip parts (InfinityFree has ~10MB FTP limit) ---
info "Step 3: Creating vendor.zip parts..."
cd "$PROJECT_DIR"
rm -f vendor.zip vendor_part_*
zip -r -q vendor.zip vendor/
VENDOR_SIZE=$(du -h vendor.zip | cut -f1)
# Split into 5MB parts for FTP upload
split -b 5M vendor.zip vendor_part_
PART_COUNT=$(ls vendor_part_* 2>/dev/null | wc -l)
ok "vendor.zip created ($VENDOR_SIZE), split into $PART_COUNT parts"

if [ "$VENDOR_ONLY" = true ]; then
    # --- Vendor-only mode: upload vendor.zip parts to htdocs/ ---
    info "Step 4: Uploading vendor.zip parts..."

    lftp -u "$FTP_USER","$FTP_PASS" "$FTP_HOST" <<LFTP_EOF
set ftp:ssl-allow no
set net:timeout 300
set net:max-retries 3
set net:reconnect-interval-base 5
cd ${HTDOCS_PATH}
$(for part in vendor_part_*; do echo "put $part"; done)
bye
LFTP_EOF

    ok "vendor.zip parts uploaded to htdocs/"

else
    # --- Full mode: upload EVERYTHING to htdocs/ (P4O pattern) ---

    # --- Step 4: Prepare dirs ---
    mkdir -p bootstrap/cache
    mkdir -p storage/framework/{sessions,views,cache}
    mkdir -p storage/logs
    mkdir -p storage/app/public
    rm -f public/.env 2>/dev/null || true

    # --- Step 5: Upload via lftp ---
    info "Step 4: Uploading to $FTP_HOST..."

    LFTP_SCRIPT=$(mktemp)
    cat > "$LFTP_SCRIPT" << ENDLFTP
set ftp:ssl-allow no
set net:timeout 30
set net:max-retries 3
set net:reconnect-interval-base 5

open ftp://${FTP_USER}:${FTP_PASS}@${FTP_HOST}

# Upload .htaccess to htdocs/ root (P4O pattern: redirects to public/)
put public/.htaccess.htdocs -o ${HTDOCS_PATH}.htaccess

# Upload deploy-setup.php to htdocs/public/ (so .htaccess rewrite finds it)
put public/deploy-setup.php -o ${HTDOCS_PATH}public/deploy-setup.php

# Upload server-reset.php to htdocs/public/
put public/server-reset.php -o ${HTDOCS_PATH}public/server-reset.php

# Upload vendor.zip parts to htdocs/ (InfinityFree has ~10MB FTP limit)
$(for part in vendor_part_*; do echo "put $part -o ${HTDOCS_PATH}${part}"; done)

# Upload env.production to htdocs/ (deploy-setup.php creates .env from it)
put .env.production -o ${HTDOCS_PATH}env.production

# Upload ALL Laravel code to htdocs/ (P4O pattern: everything in htdocs)
mirror --reverse --verbose --no-perms \
  --exclude .git/ \
  --exclude .gitignore \
  --exclude vendor/ \
  --exclude vendor.zip \
  --exclude vendor_part_* \
  --exclude node_modules/ \
  --exclude .env \
  --exclude .env.ftp \
  --exclude .env.production \
  --exclude .env.example \
  --exclude .env.testing \
  --exclude storage/logs/ \
  --exclude storage/framework/views/ \
  --exclude storage/framework/cache/ \
  --exclude storage/framework/sessions/ \
  --exclude storage/fonts/ \
  --exclude .junie/ \
  --exclude .idea/ \
  --exclude .vscode/ \
  --exclude wallet-mintme/ \
  --exclude .*\.zip \
  --exclude bootstrap/cache/ \
  --exclude public/.htaccess.htdocs \
  . ${HTDOCS_PATH}

bye
ENDLFTP

    lftp -f "$LFTP_SCRIPT"
    rm -f "$LFTP_SCRIPT"

    ok "Upload complete"
fi

# --- Done ---
SETUP_URL="${SETUP_DOMAIN}/deploy-setup.php?key=${DEPLOY_KEY}&action=setup"

echo ""
echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}  Deploy uploaded successfully!${NC}"
echo -e "${GREEN}============================================${NC}"
echo ""
echo -e "  ${YELLOW}IMPORTANT: Visit this URL in your BROWSER to complete setup:${NC}"
echo -e "  ${YELLOW}(Do NOT use curl — InfinityFree anti-bot blocks non-browser requests)${NC}"
echo ""
echo -e "  ${CYAN}${SETUP_URL}${NC}"
echo ""
echo -e "  This will:"
echo -e "  1. Extract vendor.zip -> vendor/"
echo -e "  2. Run database migrations"
echo -e "  3. Clear all caches"
echo -e "  4. Auto-delete itself"
echo ""
echo -e "${GREEN}============================================${NC}"
echo ""

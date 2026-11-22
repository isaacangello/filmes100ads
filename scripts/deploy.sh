#!/bin/bash
# deploy.sh — Build e deploy para InfinityFree via FTP
# Adaptado do padrão receitas-cei para Laravel
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_DIR="$(dirname "$SCRIPT_DIR")"

# Carregar .env
if [[ -f "$PROJECT_DIR/.env" ]]; then
    set -a
    source "$PROJECT_DIR/.env"
    set +a
fi

echo "=== Build do projeto ==="
cd "$PROJECT_DIR"
npm run build

# Remove arquivos desnecessários antes do deploy
rm -rf "$PROJECT_DIR/public/vendor/fontawesome-free" 2>/dev/null || true
rm -f "$PROJECT_DIR/public/storage" 2>/dev/null || true

echo ""
echo "=== Deploy para InfinityFree ==="
if command -v lftp &>/dev/null; then
    lftp -c "
        set ftp:ssl-allow no;
        open ftp://${FTP_USERNAME}:${FTP_PASSWORD}@${FTP_HOST};
        mirror --reverse --delete --verbose --no-perms public/ /${DEPLOY_PATH};
        quit
    "
    echo "Deploy concluido!"
else
    echo "ERRO: lftp nao encontrado. Instale com: sudo apt install lftp"
    exit 1
fi

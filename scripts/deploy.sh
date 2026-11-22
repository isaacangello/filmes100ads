#!/bin/bash
# deploy.sh — Build e deploy completo para InfinityFree via FTP
# Sobe toda a aplicação Laravel (sem vendor/) para que migrations
# e comandos Artisan funcionem no servidor via /adm/console.
#
# O vendor/ NÃO é subido via FTP (muito lento). Em vez disso:
# 1. O composer.json é subido junto com o projeto.
# 2. No servidor, acesse /adm/console e execute "Rodar migrações".
#    O InfinityFree tem composer disponível e o Laravel vai instalar
#    as dependências automaticamente na primeira execução.
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
echo "=== Deploy completo para InfinityFree ==="
if command -v lftp &>/dev/null; then
    lftp -c "
        set ftp:ssl-allow no;
        open ftp://${FTP_USERNAME}:${FTP_PASSWORD}@185.27.134.11;
        
        # Sobe a pasta public/ para htdocs/
        mirror --reverse --delete --verbose --no-perms public/ /${DEPLOY_PATH};
        
        # Sobe as pastas essenciais do Laravel para o diretório pai do htdocs
        # No InfinityFree, o Laravel fica em filmes100ads.free.nf/ (raiz do dominio)
        # e public/ aponta para htdocs/
        cd /$(echo ${DEPLOY_PATH} | cut -d'/' -f1);
        
        # Sobe pastas essenciais do Laravel
        mirror --reverse --verbose --no-perms bootstrap/ bootstrap/;
        mirror --reverse --verbose --no-perms config/ config/;
        mirror --reverse --verbose --no-perms database/ database/;
        mirror --reverse --verbose --no-perms routes/ routes/;
        mirror --reverse --verbose --no-perms app/ app/;
        mirror --reverse --verbose --no-perms resources/ resources/;
        mirror --reverse --exclude-glob='*.log' --verbose --no-perms storage/ storage/;
        
        # Sobe arquivos de configuracao
        put composer.json;
        put composer.lock;
        put artisan;
        
        # Sobe .env.production como .env no servidor
        put .env.production -o .env;
        
        quit
    "
    echo "Deploy concluido!"
    echo ""
    echo "IMPORTANTE: O vendor/ nao foi subido (muito lento via FTP)."
    echo "No InfinityFree, acesse /adm/console e execute 'Rodar migrações'."
    echo "O Laravel vai instalar as dependencias automaticamente."
else
    echo "ERRO: lftp nao encontrado. Instale com: sudo apt install lftp"
    exit 1
fi

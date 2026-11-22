# Deploy Setup Plan — filmes100ads

## Overview

Configurar ambiente de desenvolvimento e produção seguindo o padrão do projeto `receitas-cei`:
- Variáveis de ambiente separadas para dev e produção no `.env`
- Script de deploy manual via FTP (`scripts/deploy.sh`)
- Deploy automático via GitHub Actions (`.github/workflows/deploy.yml`)
- `.env.example` atualizado com todas as variáveis

---

## Current State

- `.env` já contém variáveis separadas: `DB_HOST` (local), `DB_PROD_*` (InfinityFree), `FTP_*`, `DEPLOY_PATH`
- Banco de dados InfinityFree já criado: `if0_42505744_fda`
- Nenhum `scripts/` ou `.github/workflows/` existe ainda
- `.env.example` não inclui as variáveis de deploy

---

## Steps

### Step 1: Atualizar .env.example
Adicionar todas as variáveis de deploy ao `.env.example`:
- `FTP_HOST`, `FTP_USERNAME`, `FTP_PASSWORD`
- `DB_PROD_HOST`, `DB_PROD_NAME`, `DB_PROD_USERNAME`, `DB_PROD_PASSWORD`
- `DB_LOCAL_HOST`, `DB_LOCAL_PORT`, `DB_LOCAL_DATABASE`, `DB_LOCAL_USERNAME`, `DB_LOCAL_PASSWORD`
- `DEPLOY_PATH`, `DEPLOY_KEY`, `ADMIN_KEY`
- `TMDB_API_KEY`

### Step 2: Criar scripts/deploy.sh
Script de deploy manual via FTP seguindo o padrão do receitas-cei:
- Build do Vite (`npm run build`)
- Mirror da pasta `public/` via `lftp` para InfinityFree
- Enviar `.env` de produção separado

### Step 3: Criar .github/workflows/deploy.yml
Workflow GitHub Actions:
- Dispara no push de tag `v*` ou `workflow_dispatch`
- Instala dependências, faz build
- Deploy via FTP usando secrets
- Envia `.env` de produção via secret `DOTENV`

### Step 4: Popular banco local com TMDB
- Executar o seeder `TmdbMoviesSeeder` no banco local MySQL
- Verificar que os filmes foram criados corretamente

---

## Risks

| Risco | Mitigação |
|---|---|
| InfinityFree não aceita CHMOD | Usar `--no-perms` no lftp como no receitas-cei |
| .env com credenciais no git | Garantir que `.env` está no `.gitignore` |
| Laravel precisa de `php artisan` no servidor | Deploy inclui apenas build estático + PHP source |

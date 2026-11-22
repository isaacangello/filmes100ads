# Filmes 100 ADS ou FDA

Projeto de portifólio funcional escrito em PHP 8.2+ utilizando **Laravel 11** (migrado de Laravel 5.8/6).

Esse site contem um sistema de gerenciamento de usuários, com cadastro de dados em banco de dados relacional (MySQL).

### Telas do Projeto

As telas do projeto são divididas em duas seções principais, HOME e ADM, onde a home é onde os links são exibidos para os usuários finais, e adm é onde se faz o gerenciamento dos dados, onde crud funciona.
   A sessão adm está dividida nas seguintes telas.   

```
LOGIN
ADM
CADASTRO DE USUÁRIOS
CADASTRO DE LINKS
RENDIMENTOS (IMPLEMENTADO - tracking de visualizações)
BUSCA DE TORRENTS (IMPLEMENTADO - 3 fontes públicas)
 ```
    
### Observação.

Todos os Cadastros são feitos e registrados pelo sistema usando como referência o id do usuário fornecido pela classe Auth do Laravel.

### Identidade visual

O projeto inteiro (área pública e ADM) usa **Tailwind CSS v4** com configuração *CSS-first* via `@theme`,
empacotado pelo **Vite**. O Bootstrap 4, o SB Admin 2 e o jQuery foram removidos.

- Tokens e componentes base: `resources/css/app.css` (`@theme` com as cores `ink-*`, `brand`, `chalk`, `muted`…)
- Layout público: `resources/views/layouts/public.blade.php` (topbar, hero, chips de categoria, rodapé em colunas)
- Layout do ADM: `resources/views/layouts/admin.blade.php` (sidebar, indicadores, últimos filmes, ações rápidas)
- Pôsteres em 2:3, abas de servidores no detalhe do filme e recorte de capa com **Croppie** (npm)

---

## Stack atual

- **PHP** ^8.2 / **Laravel** ^11
- **MySQL 8** (rodando via Docker no ambiente de desenvolvimento)
- **Tailwind CSS** ^4 + **Vite** (front-end completo, sem Bootstrap/jQuery)
- **Livewire** ^3 (interatividade progressiva; Alpine.js vem junto)
- `laravel/sanctum` (autenticação de API), `intervention/image-laravel` (crop de capas), `croppie` (recorte no navegador)
- Autenticação própria no estilo Breeze em `app/Http/Controllers/Auth/` (o pacote `laravel/ui` não é compatível com Laravel 11)
- Testes com `pestphp/pest` (phpunit)

## Como rodar (desenvolvimento)

1. Suba o MySQL no Docker (na porta 3306) e crie o banco:
   ```bash
   docker exec mysql_db mysql -uroot -ppassword -e "CREATE DATABASE IF NOT EXISTS filmes100ads CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```
2. Instale as dependências e configure o ambiente:
   ```bash
   composer install
   npm install
   cp .env.example .env
   php artisan key:generate
   php artisan migrate
   php artisan db:seed   # opcional: popula com filmes reais da TMDB API
   php artisan storage:link
   ```
3. Compile os assets (ou rode o Vite em watch):
   ```bash
   npm run build     # produção
   npm run dev       # watch
   ```
4. Suba o servidor:
   ```bash
   php artisan serve
   ```

- HOME pública: `http://localhost:8000/`
- Busca de torrents: `http://localhost:8000/torrents`
- Área ADM (requer login): `http://localhost:8000/adm`
- Testes: `php artisan test`

### Dados de exemplo

O seeder usa a **TMDB API** para buscar filmes reais dos últimos 20 anos (2006–2026) com trailers do YouTube.

1. Obtenha uma chave gratuita em [https://www.themoviedb.org/settings/api](https://www.themoviedb.org/settings/api)
2. Adicione ao `.env`: `TMDB_API_KEY=sua_chave_aqui`
3. Execute: `php artisan db:seed --class=TmdbMoviesSeeder`

Sem a chave configurada, o seeder mostra uma mensagem e pula a importação.

Dois usuários são pré-cadastrados automaticamente:
- `isaacangello@gmail.com` / `password`
- `isaacangello@inf.ufpel.edu.br` / `password`

---

## Funcionalidades Implementadas

### Busca de Torrents
Componente Livewire que agrega resultados de 3 fontes públicas configuráveis:
- **SolidTorrents** — API pública de torrents
- **Nyaa** — Tracker público de torrents
- **Archive.org** — Acervo público de domínio público

Configuração em `config/torrents.php` para ativar/desativar fontes manualmente.
Cache de 5 minutos e timeout de 5 segundos por provider.

### Rendimentos
Sistema de tracking de visualizações com dashboard no ADM:
- Registro automático ao visualizar filmes
- Deduplicação por IP hash
- Cálculo de ganhos baseado em visualizações
- Histórico de saques com status

### Melhorias no ADM
- Filtros por `status1`/`status2` na lista de filmes
- Exclusão de filmes com confirmação (Alpine.js modal)
- Verificação de ownership (apenas dono pode excluir)
- Edição completa em `/adm/cadastro/{post}/editar`, com dados pré-preenchidos
- Atualização de categoria e servidores, mantendo ou substituindo a capa com recorte

### Dados Reais via TMDB API
O seeder `TmdbMoviesSeeder` busca filmes reais da TMDB API:
- Até 20.000 filmes dos últimos 20 anos
- Dados completos: título, sinopse, ano, duração, país, gêneros
- 1 trailer do YouTube por filme como primeiro servidor
- Categorias mapeadas automaticamente dos gêneros TMDB

---

## Arquivos de Acompanhamento

- `AGENTS.md` — Diretrizes do projeto para agentes de IA
- `TODO.md` — Lista de pendências organizadas por prioridade
- `ESTADO-DO-PROJETO.md` — Resumo completo do estado atual
- `ANALISE-NVIDIA-CONTENT-SAFETY.md` — Investigação do erro de content safety

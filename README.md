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
RENDIMENTOS (NÃO IMPLEMENTADO) 

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
   php artisan db:seed   # opcional: popula categorias + 5000 filmes de exemplo
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

Após `db:seed`, são criados 5000 filmes fictícios distribuídos em categorias.
Cadastre um usuário pela tela `/register` para acessar a área ADM.

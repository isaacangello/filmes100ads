# AGENTS.md

## Sobre o projeto

`filmes100ads` é um **projeto de estudo/portfólio**, sem previsão de ir ao ar. É um catálogo de filmes em Laravel, com área pública e administrativa. O fato de ser apenas estudo não dispensa cuidado jurídico nem de segurança: o código pode ser usado como referência em outro contexto.

Stack atual:

- PHP 8.2+, Laravel 11
- MySQL no ambiente local
- Livewire 3
- Tailwind CSS 4 com configuração CSS-first (`@theme`) e Vite
- Pest/PHPUnit

## Estado atual e preservação do trabalho

- A branch de trabalho é `newlaravel`.
- O diretório pode conter alterações locais não commitadas. **Trate-as como trabalho do usuário**: não faça `reset`, `checkout`, `stash`, `clean` nem substitua arquivos sem autorização explícita.
- Antes de qualquer alteração, execute `git status --short` e `git diff`.
- Não faça commits, pushes ou mudanças em arquivos fora do escopo solicitado, salvo pedido explícito.
- A rota `/__qa-login` vista no working tree é temporária e faz login automático. Não a use como padrão de produção; remova-a antes de qualquer commit, a menos que o usuário peça o contrário.
- `.env` e credenciais nunca devem ser lidas em voz alta, copiadas para documentos, logs ou commits.

## Arquitetura e limites atuais

- `routes/web.php`: rotas públicas e administrativas.
- `app/Http/Controllers/`: controllers tradicionais.
- `app/Livewire/`: 6 componentes — `MovieCatalog`, `MovieDetail`, `AdminMovieForm`, `AdminMovieList`, `TorrentSearch`, `EarningsDashboard`.
- `app/Services/Torrent/`: serviço de busca de torrents com provider pattern (interface + 3 providers: SolidTorrents, Nyaa, Archive.org).
- `app/Services/TmdbService.php`: serviço para buscar filmes reais na TMDB API.
- `app/Services/EarningsService.php`: serviço de cálculo de rendimentos baseado em visualizações.
- `app/Models/MovieView.php`: model para tracking de visualizações de filmes.
- `app/Models/Withdrawal.php`: model para histórico de saques.
- `config/torrents.php`: configuração dos providers de torrent (ativar/desativar fontes manualmente).
- `resources/views/livewire/`: views dos componentes Livewire.
- `resources/views/catalog/`, `adm/` e `layouts/`: páginas e layouts Tailwind.
- `database/migrations/2026_11_19_000001_create_movie_views_table.php`: tracking de visualizações.
- `database/migrations/2026_11_19_000002_create_withdrawals_table.php`: histórico de saques.
- `database/seeders/TmdbMoviesSeeder.php`: seeder que busca até 20.000 filmes da TMDB API.
- `scripts/deploy.sh`: script de deploy manual via FTP para InfinityFree.
- `.github/workflows/deploy.yml`: deploy automático via GitHub Actions (tag `v*` ou `workflow_dispatch`).
- `app/Models/CoinHiveApi.php` é código legado morto; não há minerador ativo nas páginas.
- Não existe integração com a API NVIDIA neste projeto. O erro `input rejected by content safety screening` ocorreu na camada de API usada pelo agente OpenCode, não no Laravel.

### Funcionalidades implementadas (Fase 2 do plano)

- **Busca de torrents**: Componente `TorrentSearch` com busca por termo, filtro por fonte e ordenação. Service `TorrentService` com cache e 3 providers configuráveis via `config/torrents.php`.
- **Rendimentos**: Tracking de visualizações em `movie_views`, cálculo de ganhos via `EarningsService`, dashboard com cards de estatísticas e histórico de saques.
- **Melhorias ADM**: Filtros por `status1`/`status2`, exclusão com modal de confirmação (Alpine.js) e edição completa em `/adm/cadastro/{post}/editar`, com pré-preenchimento, verificação de proprietário e substituição opcional da capa.

### Limites conhecidos

- Os providers de torrent usam APIs públicas que podem estar indisponíveis; timeout de 5s e cache de 5min são usados como mitigação.
- O tracking de visualizações deduplica por IP hash, mas não há agregação periódica dos dados brutos.
- O seeder TMDB requer `TMDB_API_KEY` configurado no `.env`. Sem a chave, o seeder pula a importação de filmes.
- Os trailers do YouTube não são buscados pelo seeder — os 6 servidores são placeholders. Os trailers podem ser buscados sob demanda no futuro.

## Deploy

### Ambiente de desenvolvimento
- Banco local: MySQL via Docker (`127.0.0.1:3306`)
- `.env` com `DB_HOST=127.0.0.1` para dev
- `php artisan serve` para rodar localmente

### Ambiente de produção (InfinityFree)
- Banco remoto: `sql202.infinityfree.com` (`if0_42505744_fda`)
- Variáveis de produção no `.env`: `DB_PROD_*`, `FTP_*`, `DEPLOY_PATH`
- Deploy manual: `./scripts/deploy.sh` (requer `lftp`)
- Deploy automático: push de tag `v*` ou `workflow_dispatch` no GitHub Actions
- GitHub Secrets necessários: `FTP_HOST`, `FTP_USER`, `FTP_PASS`, `DOTENV`

### .env
- O `.env` contém variáveis separadas para dev e produção
- `.env.example` lista todas as variáveis com valores placeholder
- Nunca commitar `.env` com credenciais reais

## Convenções

- Indentação de 4 espaços, finais de linha LF e newline final, conforme `.editorconfig`.
- Comentários e documentação em português, quando existirem.
- Preserve o padrão PSR-12-ish do código PHP existente.
- Não reintroduza Bootstrap, SB Admin 2, jQuery, jCrop ou webpack.mix.js.
- Use os tokens e componentes definidos em `resources/css/app.css` (`@theme`, classes `fda-*`).
- Em Livewire, mantenha filtros/parâmetros de URL coerentes com `MovieCatalog`, `MovieDetail` e os componentes administrativos existentes.
- Para novos recursos, prefira service classes pequenas e providers explícitos em vez de lógica misturada em controllers ou views.

## Critérios éticos e de segurança

- O protocolo BitTorrent não é ilegal por si só, mas este projeto não deve ser usado para localizar, linkar ou distribuir obras protegidas sem autorização.
- Se a busca de torrents for implementada, limite-a a fontes legais, domínio público, Creative Commons ou acervos autorizados, com filtragem e documentação da origem.
- Não implemente mineração em navegador usando CPU de visitantes sem consentimento explícito, informativo claro e possibilidade de parar. Não trate o endereço de carteira presente em uma view como autorização para usar recursos de terceiros.
- Não tente disfarçar, fragmentar ou reformular conteúdo apenas para escapar de filtros de segurança de providers. Diagnóstico deve ser feito com prompts deliberadamente explícitos e separados, não com evasão.
- Em sessões de agente, evite acumular screenshots extensas e contexto irrelevante. Prefira sessões novas e escopo mínimo ao investigar falhas de API.

## Comandos úteis

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed       # opcional: dados de exemplo (requer TMDB_API_KEY)
php artisan storage:link
npm run dev               # Vite em modo watch
npm run build             # build de produção
php artisan serve
php artisan test
php artisan route:list --except-vendor
./scripts/deploy.sh       # deploy manual para InfinityFree
```

Os testes usam SQLite em memória conforme `phpunit.xml`. Após alterações em PHP, execute `php artisan test`. Após alterações em CSS/JS/Blade, execute também `npm run build`.

## Diagnóstico do erro de safety da NVIDIA

O histórico completo da investigação está em `ANALISE-NVIDIA-CONTENT-SAFETY.md`. Em resumo: o erro é do screening externo da NVIDIA, não do Laravel; a documentação oficial indica que MiniMax e GLM usam o mesmo endpoint de chat. O payload da sessão afetada era extremamente longo e continha contexto de torrents e de mineração com carteira Bitcoin. Isso torna a mudança do projeto para torrents uma hipótese plausível, mas não prova isolada nem classifica automaticamente a ação como violação.

Antes de fazer chamadas de diagnóstico a providers externos, obtenha autorização e remova segredos. Registre status HTTP, mensagem e request ID, se houver.

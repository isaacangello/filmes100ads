---
sessionId: session-261119-084832-bnnj
---

# Requirements

## Overview

Implementar as frentes pendentes do projeto `filmes100ads` em ordem de prioridade:
1. **Busca de torrents** — componente Livewire + service configurável com fontes públicas
2. **Rendimentos** — tracking de visualizações e dados reais na tela ADM
3. **Melhorias ADM** — filtros por status, exclusão e edição de filmes

---

## In Scope

### Fase 1 — Busca de Torrents
- Componente Livewire `TorrentSearch` com busca por termo, paginação e filtros por fonte
- Service `TorrentService` com interface/provider pattern para adicionar fontes manualmente
- 3 providers implementados: **SolidTorrents**, **Nyaa**, **Archive.org** (APIs públicas)
- Resultados exibidos com título, tamanho, seeders, link magnet/download
- Filtros: fonte, ordenação (seeders, tamanho, data)
- Configuração das fontes via `config/torrents.php`
- Atualizar view `torrents/index.blade.php` para usar o componente Livewire
- Testes feature para o componente e service

### Fase 2 — Rendimentos
- Migration para tracking de visualizações (`movie_views`)
- Service `EarningsService` para calcular ganhos por visualização
- Componente Livewire `EarningsDashboard` com dados reais
- Cards atualizados: ganho total, visualizações, downloads
- Histórico de saques (tabela `withdrawals`)
- Atualizar `RendimentosController` e `earnings.blade.php`
- Testes feature

### Fase 3 — Melhorias ADM
- **Status filters**: adicionar filtros por `status1`/`status2` na lista ADM (`AdminMovieList`)
- **Excluir filmes**: botão de exclusão com confirmação (Alpine.js modal)
- **Editar filmes**: pré-preencher formulário com dados existentes, salvar atualizações

---

## Out of Scope
- Mineração em navegador (CoinHive/CoinIMP) — código legado mantido inativo
- Integração com APIs de pagamento ou conversão BTC
- Remoção do código morto (`CoinHiveApi.php`) — apenas documentado
- Rota `/__qa-login` — mantida como temporária

---

## User Stories

1. **Como visitante**, quero buscar torrents por título e ver resultados agregados de fontes públicas, para encontrar arquivos legais de filmes.
2. **Como administrador**, quero ver meus rendimentos reais baseados em visualizações, para acompanhar o desempenho das minhas publicações.
3. **Como administrador**, quero filtrar meus filmes por status, para gerenciar melhor o fluxo de aprovação.
4. **Como administrador**, quero excluir filmes que não preciso mais, para manter o catálogo limpo.
5. **Como administrador**, quero editar filmes cadastrados, para corrigir dados sem precisar recadastrar.

---

## Acceptance Criteria

- A busca de torrents retorna resultados das 3 fontes configuradas
- O componente `TorrentSearch` suporta filtros por fonte e ordenação
- A tela de rendimentos mostra dados reais de visualizações
- A lista ADM permite filtrar por status, excluir e editar filmes
- Todos os testes existentes continuam passando
- Novos testes cobrem as funcionalidades implementadas

# Technical Design

## Current Implementation

### Arquitetura existente
- **Rotas**: `routes/web.php` com 20 rotas (públicas, auth, ADM)
- **Controllers**: tradicionais em `app/Http/Controllers/`
- **Livewire**: 4 componentes (`MovieCatalog`, `MovieDetail`, `AdminMovieForm`, `AdminMovieList`)
- **Models**: `Post`, `Categoria`, `Urls`, `User`, `CoinHiveApi` (legado)
- **Views**: separadas em `catalog/`, `adm/`, `livewire/`, `layouts/`
- **Testes**: 13 feature tests (CatalogTest, AdminMoviesTest) com `LivewireTestCase` base
- **Banco**: 8 migrations (users, posts, categorias, categorias_posts, urls, sessions, etc.)

### Padrões identificados
- Filtros de URL via `#[Url]` attribute no Livewire (ex: `MovieCatalog`)
- Paginação customizada com `pagination::fda`
- Service classes pequenas preferidas (convenção em AGENTS.md)
- Tokens Tailwind via `@theme` em `resources/css/app.css` (classes `fda-*`)
- Views Blade com `@extends('layouts.public')` ou `@extends('layouts.admin')`

---

## Key Decisions

### 1. Provider Pattern para Torrents
Cada fonte de torrent será um provider que implementa uma interface `TorrentProviderInterface`. Isso permite adicionar novas fontes sem modificar o componente Livewire.

```
graph LR
    T[TorrentSearch Livewire] --> S[TorrentService]
    S --> P1[SolidTorrentsProvider]
    S --> P2[NyaaProvider]
    S --> P3[ArchiveOrgProvider]
    S --> C[config/torrents.php]
```

### 2. Configuração manual das fontes
As fontes serão configuradas em `config/torrents.php` com URLs, headers e opções de cada API. O usuário pode ativar/desativar fontes sem código.

### 3. Tracking de visualizações
Uma nova tabela `movie_views` registrará cada visualização de filme (user_id, post_id, ip_hash, created_at). O `EarningsService` calculará ganhos com base em um valor por visualização configurável.

### 4. Edição de filmes no ADM
O `AdminMovieForm` será estendido para suportar edição: se receber um `post_id`, pré-preenche os campos e salva como update em vez de create.

---

## Proposed Changes

### Fase 1 — Busca de Torrents

**Novos arquivos:**
- `app/Services/Torrent/TorrentService.php` — orquestra a busca entre providers
- `app/Services/Torrent/TorrentProviderInterface.php` — interface para providers
- `app/Services/Torrent/Providers/SolidTorrentsProvider.php`
- `app/Services/Torrent/Providers/NyaaProvider.php`
- `app/Services/Torrent/Providers/ArchiveOrgProvider.php`
- `app/Models/TorrentResult.php` — data object para resultados
- `app/Livewire/TorrentSearch.php` — componente de busca
- `resources/views/livewire/torrent-search.blade.php` — view do componente
- `config/torrents.php` — configuração das fontes
- `tests/Feature/TorrentSearchTest.php` — testes

**Arquivos modificados:**
- `resources/views/torrents/index.blade.php` — substituir placeholder pelo componente Livewire
- `app/Http/Controllers/TorrentController.php` — simplificar (apenas renderizar view)

### Fase 2 — Rendimentos

**Novos arquivos:**
- `database/migrations/xxxx_create_movie_views_table.php` — tracking de visualizações
- `database/migrations/xxxx_create_withdrawals_table.php` — histórico de saques
- `app/Models/MovieView.php` — model
- `app/Models/Withdrawal.php` — model
- `app/Services/EarningsService.php` — cálculos de rendimento
- `app/Livewire/EarningsDashboard.php` — componente com dados reais
- `resources/views/livewire/earnings-dashboard.blade.php` — view
- `tests/Feature/EarningsTest.php` — testes

**Arquivos modificados:**
- `app/Http/Controllers/RendimentosController.php` — conectar ao componente Livewire
- `resources/views/adm/earnings.blade.php` — usar componente Livewire
- `app/Livewire/MovieDetail.php` — registrar visualização ao carregar

### Fase 3 — Melhorias ADM

**Arquivos modificados:**
- `app/Livewire/AdminMovieList.php` — adicionar filtros por status, ação de excluir
- `resources/views/livewire/admin-movie-list.blade.php` — UI de filtros, botões de ação, modal de confirmação
- `app/Livewire/AdminMovieForm.php` — suportar edição (mount com post_id, save como update)
- `resources/views/livewire/admin-movie-form.blade.php` — mostrar modo edição
- `app/Http/Controllers/CadController.php` — rota de edição
- `routes/web.php` — adicionar rota de edição
- `tests/Feature/AdminMoviesTest.php` — testes de edição e exclusão

---

## Data Models

### TorrentResult
```php
class TorrentResult {
    public string $title;
    public string $magnet;
    public string $size;        // ex: "1.2 GB"
    public int $seeders;
    public int $leechers;
    public string $source;      // 'solidtorrents', 'nyaa', 'archiveorg'
    public string $url;         // link para detalhes
    public ?string $category;
}
```

### MovieView
```php
// Tabela: movie_views
- id (bigIncrements)
- post_id (bigint, foreign key)
- user_id (bigint, nullable)
- ip_hash (string, 64 chars)
- created_at (timestamp)
```

### Withdrawal
```php
// Tabela: withdrawals
- id (bigIncrements)
- user_id (bigint, foreign key)
- amount_mint (decimal)
- amount_btc (decimal)
- status (string: 'pending', 'completed', 'rejected')
- btc_address (string)
- created_at (timestamp)
```

### config/torrents.php
```php
return [
    'providers' => [
        'solidtorrents' => [
            'enabled' => true,
            'base_url' => 'https://solidtorrents.to/api',
            'headers' => [],
        ],
        'nyaa' => [
            'enabled' => true,
            'base_url' => 'https://nyaa.si/api?',
            'headers' => [],
        ],
        'archiveorg' => [
            'enabled' => true,
            'base_url' => 'https://archive.org/advancedsearch.php',
            'headers' => [],
        ],
    ],
    'filters' => [
        'categories' => ['movies', 'film', 'video'],
        'min_seeders' => 0,
    ],
];
```

---

## Risks

| Risco | Mitigação |
|---|---|---|---|
| APIs de torrent podem estar indisponíveis | Timeout curto (5s), fallback graceful com mensagem de erro | | | |
| Rate limiting das APIs | Cache dos resultados (5 min) via `Cache::remember()` | | | |
| Dados sensíveis em config/torrents.php | Adicionar ao .gitignore, usar env vars se necessário | | | |
| Tracking de visualizações pode crescer muito | Agregar periodicamente, manter apenas 30 dias de dados brutos | | | |
| Edição de filmes com conflitos de usuário | Verificar ownership (apenas dono pode editar) | | | |

# Testing

## Validation Approach

Após cada fase, executar `php artisan test` para validar:
1. Todos os testes existentes continuam passando (regressão)
2. Novos testes cobrem as funcionalidades implementadas
3. Build do Vite (`npm run build`) após mudanças em CSS/Blade

---

## Fase 1 — TorrentSearchTest

### Key Scenarios
- `test_torrent_search_returns_results_from_providers` — busca retorna resultados quando providers respondem
- `test_torrent_search_filters_by_source` — filtro por fonte funciona
- `test_torrent_search_handles_provider_timeout` — provider com timeout não quebra a busca
- `test_torrent_search_shows_empty_state` — mensagem quando não há resultados
- `test_torrent_service_aggregates_results` — service agrega resultados de múltiplos providers
- `test_solid_torrents_provider_parses_response` — parser do SolidTorrents funciona
- `test_nyaa_provider_parses_response` — parser do Nyaa funciona
- `test_archive_org_provider_parses_response` — parser do Archive.org funciona

### Edge Cases
- Provider retorna erro HTTP (500, 403)
- Provider não responde (timeout)
- Configuração com provider desabilitado
- Busca vazia retorna mensagem adequada
- Resultado sem magnet link é tratado

---

## Fase 2 — EarningsTest

### Key Scenarios
- `test_viewing_a_movie_records_a_view` — visualização é registrada
- `test_earnings_dashboard_shows_real_data` — dashboard mostra dados reais
- `test_earnings_calculates_gains_correctly` — cálculo de ganhos está correto
- `test_withdrawal_history_is_empty_by_default` — histórico vazio por padrão
- `test_only_authenticated_users_are_tracked` — apenas usuários logados são trackados

### Edge Cases
- Múltiplas visualizações do mesmo usuário (deduplicação por IP)
- Visualização sem usuário logado (guest)
- Cálculo com zero visualizações

---

## Fase 3 — AdminMoviesTest (extensão)

### Key Scenarios
- `test_admin_movie_list_filters_by_status` — filtro por status funciona
- `test_user_can_delete_their_own_movie` — exclusão funciona para dono
- `test_user_cannot_delete_another_users_movie` — não pode excluir filme de outro
- `test_user_can_edit_their_own_movie` — edição funciona para dono
- `test_edit_pre_fills_form_with_existing_data` — formulário é pré-preenchido
- `test_edit_saves_updates_correctly` — update salva corretamente

### Edge Cases
- Tentar editar filme inexistente (404)
- Tentar excluir filme de outro usuário (403)
- Formulário de edição com campos inválidos (validação)

# Delivery Steps

### ✓ Step 1: Fase 1 — Estrutura de Torrents
Criar a infraestrutura de busca de torrents: interface, service, providers e configuração.
- Criar `TorrentProviderInterface` com método `search($query): TorrentResult[]`
- Criar `TorrentService` que orquestra a busca entre providers com timeout e cache
- Implementar 3 providers: SolidTorrents, Nyaa, Archive.org
- Criar `TorrentResult` model (data object)
- Criar `config/torrents.php` com configuração das fontes
- Criar testes unitários para o service e providers (mock das respostas HTTP)
- Executar `php artisan test` para validar

### ✓ Step 2: Fase 1 — Componente TorrentSearch
Implementar o componente Livewire e a view de busca de torrents.
- Criar `TorrentSearch` Livewire com busca por termo, filtro por fonte e ordenação
- Criar `resources/views/livewire/torrent-search.blade.php` com UI Tailwind
- Atualizar `resources/views/torrents/index.blade.php` para usar o componente
- Simplificar `TorrentController` (apenas renderizar view)
- Criar `tests/Feature/TorrentSearchTest.php` com testes de integração
- Executar `php artisan test` e `npm run build` para validar

### ✓ Step 3: Fase 2 — Tracking de Visualizações
Implementar o sistema de tracking de visualizações de filmes.
- Criar migration `movie_views` (post_id, user_id, ip_hash, created_at)
- Criar migration `withdrawals` (user_id, amount_mint, amount_btc, status, btc_address)
- Criar models `MovieView` e `Withdrawal`
- Criar `EarningsService` com métodos: trackView(), getStats(), calculateEarnings()
- Atualizar `MovieDetail` para registrar visualização ao carregar
- Executar `php artisan migrate` e `php artisan test` para validar

### ✓ Step 4: Fase 2 — Dashboard de Rendimentos
Implementar o dashboard de rendimentos com dados reais.
- Criar `EarningsDashboard` Livewire com cards de estatísticas
- Criar `resources/views/livewire/earnings-dashboard.blade.php` com UI Tailwind
- Atualizar `RendimentosController` para usar o componente
- Atualizar `resources/views/adm/earnings.blade.php` para usar o componente
- Criar `tests/Feature/EarningsTest.php`
- Executar `php artisan test` e `npm run build` para validar

### ✓ Step 5: Fase 3 — Melhorias ADM
Adicionar filtros por status, exclusão e edição de filmes no ADM.
- Estender `AdminMovieList` com filtros por status1/status2 e ação de excluir
- Atualizar view `admin-movie-list.blade.php` com UI de filtros, botões e modal de confirmação (Alpine.js)
- Estender `AdminMovieForm` para suportar edição (mount com post_id, save como update)
- Adicionar rota de edição em `routes/web.php`
- Atualizar `CadController` com método de edição
- Estender `tests/Feature/AdminMoviesTest.php` com testes de edição e exclusão
- Executar `php artisan test` e `npm run build` para validar
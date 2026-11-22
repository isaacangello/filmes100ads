---
sessionId: session-261121-171301-17co
---

# Otimização de Performance — filmes100ads

## Requisitos

### Visão Geral
Analisar e corrigir gargalos de performance no site `filmes100ads`, focando em índices de banco de dados, consultas SQL otimizadas, e técnicas de busca FULLTEXT.

### Objetivos
- Reduzir o tempo de resposta das consultas ao banco de dados.
- Adicionar índices faltantes nas tabelas mais consultadas.
- Substituir buscas `LIKE '%termo%'` por consultas `FULLTEXT ... AGAINST` onde aplicável.
- Otimizar consultas que usam `whereDate`/`whereMonth`/`whereYear` (que impedem o uso de índices).

## Technical Design

### Gargalos Identados

#### 1. Tabela `posts` — sem índices secundários
**Migration**: `2019_09_13_040140_create_posts_table.php`

A tabela `posts` possui apenas o índice primário em `id`. As colunas usadas em filtros não têm índice:

| Coluna | Usada em | Impacto |
|--------|----------|---------|
| `user_id` | `EarningsService::getStats()`, `AdminMovieList`, `AdmController` | Full table scan em cada consulta por usuário |
| `status1` | `AdminMovieList` filtro | Full table scan |
| `status2` | `AdminMovieList` filtro | Full table scan |
| `ano` | Potencial filtro futuro | Full table scan |
| `name` | `MovieCatalog` busca `LIKE '%termo%'`, `AdminMovieList` busca | Full table scan, sem FULLTEXT |
| `realname` | `MovieDetail::fetchTorrents()` | Não crítico |

**Consulta problemática** (`MovieCatalog::render()`):
```php
$query = Post::query()->select('id', 'name', 'sinopse', 'ano', 'duracao', 'poster_url');
// ...
$query->where('name', 'like', '%'.$termo.'%');  // Full table scan
$posts = $query->orderBy('id')->paginate(self::PER_PAGE);
```

**Consulta problemática** (`EarningsService::getStats()`):
```php
$posts = Post::where('user_id', $userId)->pluck('id');  // Sem índice em user_id
$viewsTotal = MovieView::whereIn('post_id', $posts)->count();
$viewsToday = MovieView::whereIn('post_id', $posts)
    ->whereDate('created_at', today())  // whereDate impede uso de índice
    ->count();
```

#### 2. Tabela `categorias_posts` — sem índice em `categoria_id`
**Migration**: `2019_09_13_040340_create_categorias_posts_table.php`

A tabela pivot `categorias_posts` não tem índice em `categoria_id`, usado em:

```php
// MovieCatalog::render() — filtro por categoria
$query->whereIn('id', DB::table('categorias_posts')
    ->select('post_id')
    ->where('categoria_id', (int) $this->categoriaId));  // Full table scan
```

#### 3. Tabela `movie_cast` — sem índice em `post_id`
**Migration**: `2026_11_22_100454_create_movie_cast_table.php`

A foreign key `post_id` não tem índice. A consulta `Post::with('cast')` em `MovieDetail` faz um scan completo da tabela `movie_cast` para cada filme.

#### 4. Busca `LIKE '%termo%'` sem FULLTEXT
Três componentes usam `LIKE '%termo%'` para busca por título:

- `MovieCatalog::render()` — busca pública por nome do filme
- `AdminMovieList::render()` — busca ADM por nome do filme
- `UserManager::render()` — busca ADM por nome/email do usuário

Com 20.000+ filmes, `LIKE '%termo%'` faz full table scan a cada busca. A solução é usar índices FULLTEXT com `WHERE MATCH(name) AGAINST ('termo' IN BOOLEAN MODE)`.

#### 5. `whereDate`/`whereMonth`/`whereYear` em `EarningsService`
```php
$viewsToday = MovieView::whereIn('post_id', $posts)
    ->whereDate('created_at', today())  // whereDate() impede uso de índice
    ->count();
$viewsThisMonth = MovieView::whereIn('post_id', $posts)
    ->whereMonth('created_at', now()->month)  // whereMonth() impede uso de índice
    ->whereYear('created_at', now()->year)
    ->count();
```

`whereDate()` gera `DATE(created_at) = '2026-11-22'`, que aplica uma função à coluna e impede o uso de índices. A solução é usar `whereBetween()` com timestamps.

#### 6. Consulta N+1 em `MovieCatalog` para categorias
```php
$categoriaPorPost = DB::table('categorias_posts')
    ->join('categorias', 'categorias_posts.categoria_id', '=', 'categorias.id')
    ->whereIn('categorias_posts.post_id', $posts->pluck('id'))
    ->get(['categorias_posts.post_id', 'categorias.name'])
    ->groupBy('post_id')
    ->map(fn ($grupo) => $grupo->first()->name)
    ->toArray();
```

Esta consulta é feita separadamente para cada página de resultados. Poderia ser otimizada com eager loading via `Post::with('categorias')`.

### Propostas de Otimização

#### Stage 1: Índices no banco de dados
Nova migration `add_performance_indexes.php`:

```php
// posts
$table->index('user_id');
$table->index('status1');
$table->index('status2');
$table->index('ano');
$table->fullText(['name', 'realname', 'sinopse']);  // Busca FULLTEXT

// categorias_posts
$table->index('categoria_id');
$table->index('post_id');

// movie_cast
$table->index('post_id');

// users (para busca no UserManager)
$table->fullText(['name', 'email']);
```

#### Stage 2: Consultas FULLTEXT com AGAINST
Substituir `LIKE '%termo%'` por `MATCH ... AGAINST`:

**MovieCatalog**:
```php
if ($termo !== '') {
    $query->whereRaw('MATCH(name, realname) AGAINST (? IN BOOLEAN MODE)', [$termo]);
}
```

**AdminMovieList**:
```php
if ($termo !== '') {
    $query->whereRaw('MATCH(posts.name, posts.realname) AGAINST (? IN BOOLEAN MODE)', [$termo]);
}
```

**UserManager**:
```php
if ($termo !== '') {
    $query->whereRaw('MATCH(name, email) AGAINST (? IN BOOLEAN MODE)', [$termo]);
}
```

#### Stage 3: Otimizar EarningsService
Substituir `whereDate`/`whereMonth`/`whereYear` por `whereBetween`:

```php
$today = today()->startOfDay();
$tomorrow = today()->endOfDay();
$viewsToday = MovieView::whereIn('post_id', $posts)
    ->whereBetween('created_at', [$today, $tomorrow])
    ->count();

$monthStart = now()->startOfMonth();
$monthEnd = now()->endOfMonth();
$viewsThisMonth = MovieView::whereIn('post_id', $posts)
    ->whereBetween('created_at', [$monthStart, $monthEnd])
    ->count();
```

#### Stage 4: Otimizar MovieCatalog com eager loading
Substituir a consulta separada de categorias por eager loading:

```php
$query = Post::query()->with('categorias:id,name')->select(...);
// ...
// Na view, usar $filme->categorias->first()->name em vez de categoriaPorPost
```

### Arquivos Afetados

| Arquivo | Mudança |
|---------|---------|
| `database/migrations/2026_11_22_XXXXXX_add_performance_indexes.php` | Nova migration com índices |
| `app/Livewire/MovieCatalog.php` | FULLTEXT, eager loading de categorias |
| `app/Livewire/AdminMovieList.php` | FULLTEXT |
| `app/Livewire/UserManager.php` | FULLTEXT |
| `app/Services/EarningsService.php` | whereBetween em vez de whereDate/whereMonth |
| `resources/views/livewire/movie-catalog.blade.php` | Ajustar para usar eager loading |

### Riscos
- **FULLTEXT em MySQL**: Requer pelo menos 3 caracteres para funcionar (configurável via `ft_min_word_len`). Termos curtos como "a" ou "os" não serão encontrados.
- **Migration em produção**: Criar índices em tabelas grandes pode bloquear o banco brevemente. No InfinityFree, a tabela `posts` com 20.000 registros deve ser rápida.
- **Compatibilidade SQLite**: Os testes usam SQLite em memória, que não suporta FULLTEXT da mesma forma que MySQL. As consultas FULLTEXT precisarão de um fallback para SQLite nos testes.

# Delivery Steps

### ✓ Step 1: Análise de gargalos de performance
Identificados 6 gargalos: índices faltantes em `posts`, `categorias_posts`, `movie_cast`; buscas `LIKE '%termo%'` sem FULLTEXT; `whereDate`/`whereMonth` em `EarningsService`; consulta N+1 de categorias em `MovieCatalog`. O projeto filmes100ads usa Vite desde a migração para Laravel 11 — o webpack era do MintMe Wallet (delegado para outra IA).

### ✓ Step 2: Completar o código do minerador nas páginas do filmes100ads
Adicionado `addMiningNotification` ao script CoinImp em `public.blade.php` para notificação visual. Corrigido link "Política de Privacidade" no footer para usar `Alpine.store('privacy')`. Modal de privacidade atualizado para responder ao banner e ao footer. Build e 52 testes passaram.

### ✓ Step 3: Criar migration com índices de performance
Nova migration `2026_11_22_110000_add_performance_indexes.php` com índices em `posts(user_id, status1, status2, ano)`, FULLTEXT em `posts(name, realname, sinopse)` apenas em MySQL, índices em `categorias_posts(categoria_id, post_id)`, `movie_cast(post_id)` e FULLTEXT em `users(name, email)` apenas em MySQL. FULLTEXT condicional para compatibilidade com SQLite nos testes.

### ✓ Step 4: Substituir LIKE por FULLTEXT nos componentes Livewire
Convertida busca `LIKE '%termo%'` para `MATCH ... AGAINST` em `MovieCatalog`, `AdminMovieList`, e `UserManager` com fallback para SQLite nos testes via `config('database.default') === 'sqlite'`.

### ✓ Step 5: Otimizar EarningsService e MovieCatalog
Substituído `whereDate`/`whereMonth`/`whereYear` por `whereBetween` com timestamps em `EarningsService`. Substituída consulta separada de categorias por eager loading `Post::with('categorias:id,name')` em `MovieCatalog`. View `movie-catalog.blade.php` atualizada para usar `$post->categorias->first()`.
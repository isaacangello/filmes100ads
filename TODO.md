# TODO - filmes100ads

## Pendências do Projeto

### Alta Prioridade

- [x] **Edição completa de filmes no ADM** ✓
  - O `AdminMovieForm` pré-preenche os dados, a categoria e os servidores do filme
  - A edição atualiza o registro existente e preserva status e proprietário
  - A capa atual pode ser mantida ou substituída por uma nova imagem com recorte
  - O acesso ao formulário e a gravação verificam o proprietário do filme

- [x] **Configurar TMDB_API_KEY** ✓
  - Chave configurada no `.env`
  - Banco populado com 10.000 filmes reais da TMDB (2006-2026)

### Média Prioridade

- [ ] **Trailers do YouTube sob demanda**
  - O seeder cria 6 servidores placeholder por filme
  - Implementar busca de trailers no `MovieDetail` ao carregar o filme
  - Usar `TmdbService::getMovieTrailers()` para buscar o trailer do YouTube

- [ ] **Agregação periódica de visualizações**
  - Criar command artisan para agregar dados brutos de `movie_views`
  - Manter apenas 30 dias de dados brutos, agregando o restante
  - Agendar via scheduler para rodar diariamente

- [ ] **Testes de integração com APIs reais**
  - Os providers de torrent têm testes unitários com mocks
  - Faltam testes de integração que verifiquem o comportamento real das APIs
  - Cuidado com rate limiting e disponibilidade das APIs

- [ ] **Documentação da API de torrents**
  - Documentar a estrutura de resposta de cada provider
  - Incluir exemplos de uso do `TorrentService`
  - Explicar como adicionar um novo provider customizado

### Baixa Prioridade

- [ ] **Remoção do código legado**
  - `CoinHiveApi.php` pode ser removido ou movido para uma pasta `legacy/`
  - Código de mineração em views pode ser limpo
  - Manter documentação sobre o que foi removido

- [ ] **Melhorias na UI do dashboard de rendimentos**
  - Gráficos de evolução temporal das visualizações
  - Filtros por período (últimos 7 dias, 30 dias, etc.)
  - Exportação de dados para CSV

- [ ] **Configuração de providers via .env**
  - Mover URLs e tokens sensíveis do `config/torrents.php` para variáveis de ambiente
  - Adicionar ao `.gitignore` se houver credenciais no arquivo de config

## Melhorias Futuras

- [ ] Sistema de comentários nos filmes
- [ ] Avaliações por estrelas
- [ ] Lista de favoritos por usuário
- [ ] Notificações de novos filmes por categoria
- [ ] API REST para consumo externo
- [ ] Mobile responsive improvements

## Notas Técnicas

- O projeto usa SQLite em memória para testes conforme `phpunit.xml`
- Após alterações em PHP: `php artisan test`
- Após alterações em CSS/JS/Blade: `npm run build`
- Os 52 testes atuais estão passando (164 assertions)
- Build do Vite concluído com sucesso
- O seeder `TmdbMoviesSeeder` substitui o seeder antigo de 5000 filmes fictícios
- O seeder TMDB busca filmes dos últimos 20 anos (2006-2026)
- Cada filme recebe 6 servidores placeholder (trailers buscados sob demanda)

## Deploy

- [x] `.env.example` atualizado com variáveis de deploy
- [x] `scripts/deploy.sh` criado para deploy manual via FTP
- [x] `.github/workflows/deploy.yml` criado para deploy automático
- [ ] Configurar GitHub Secrets (`FTP_HOST`, `FTP_USER`, `FTP_PASS`, `DOTENV`)
- [ ] Fazer primeiro deploy para InfinityFree

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona índices de performance nas tabelas mais consultadas.
     *
     * A migration é idempotente: ignora erros 1061 (Duplicate key name)
     * para permitir re-execução segura após deploy parcial.
     */
    public function up(): void
    {
        $isMysql = config('database.default') !== 'sqlite';

        if ($isMysql) {
            // MySQL: executa cada índice, ignorando erro 1061 se já existir
            $this->runStatement('ALTER TABLE `posts` ADD INDEX `posts_user_id_index` (`user_id`)');
            $this->runStatement('ALTER TABLE `posts` ADD INDEX `posts_status1_index` (`status1`)');
            $this->runStatement('ALTER TABLE `posts` ADD INDEX `posts_status2_index` (`status2`)');
            $this->runStatement('ALTER TABLE `posts` ADD INDEX `posts_ano_index` (`ano`)');
            $this->runStatement('ALTER TABLE `posts` ADD FULLTEXT INDEX `posts_fulltext_search` (`name`, `realname`, `sinopse`)');
            $this->runStatement('ALTER TABLE `categorias_posts` ADD INDEX `categorias_posts_categoria_id_index` (`categoria_id`)');
            $this->runStatement('ALTER TABLE `categorias_posts` ADD INDEX `categorias_posts_post_id_index` (`post_id`)');
            $this->runStatement('ALTER TABLE `movie_cast` ADD INDEX `movie_cast_post_id_index` (`post_id`)');
            $this->runStatement('ALTER TABLE `users` ADD FULLTEXT INDEX `users_fulltext_search` (`name`, `email`)');
        } else {
            // SQLite: usa o Schema builder padrão
            Schema::table('posts', function ($table) {
                $table->index('user_id');
                $table->index('status1');
                $table->index('status2');
                $table->index('ano');
            });

            Schema::table('categorias_posts', function ($table) {
                $table->index('categoria_id');
                $table->index('post_id');
            });

            Schema::table('movie_cast', function ($table) {
                $table->index('post_id');
            });
        }
    }

    /**
     * Remove os índices adicionados.
     */
    public function down(): void
    {
        $isMysql = config('database.default') !== 'sqlite';

        if ($isMysql) {
            $this->runStatement('ALTER TABLE `posts` DROP INDEX `posts_user_id_index`');
            $this->runStatement('ALTER TABLE `posts` DROP INDEX `posts_status1_index`');
            $this->runStatement('ALTER TABLE `posts` DROP INDEX `posts_status2_index`');
            $this->runStatement('ALTER TABLE `posts` DROP INDEX `posts_ano_index`');
            $this->runStatement('ALTER TABLE `posts` DROP INDEX `posts_fulltext_search`');
            $this->runStatement('ALTER TABLE `categorias_posts` DROP INDEX `categorias_posts_categoria_id_index`');
            $this->runStatement('ALTER TABLE `categorias_posts` DROP INDEX `categorias_posts_post_id_index`');
            $this->runStatement('ALTER TABLE `movie_cast` DROP INDEX `movie_cast_post_id_index`');
            $this->runStatement('ALTER TABLE `users` DROP INDEX `users_fulltext_search`');
        } else {
            Schema::table('posts', function ($table) {
                $table->dropIndex(['user_id']);
                $table->dropIndex(['status1']);
                $table->dropIndex(['status2']);
                $table->dropIndex(['ano']);
            });

            Schema::table('categorias_posts', function ($table) {
                $table->dropIndex(['categoria_id']);
                $table->dropIndex(['post_id']);
            });

            Schema::table('movie_cast', function ($table) {
                $table->dropIndex(['post_id']);
            });
        }
    }

    /**
     * Executa uma statement SQL, ignorando erros de índice duplicado (1061).
     */
    protected function runStatement(string $sql): void
    {
        try {
            DB::statement($sql);
        } catch (\Exception $e) {
            // Ignora erro 1061: Duplicate key name — o índice já existe
            if (strpos($e->getMessage(), '1061') !== false) {
                return;
            }
            throw $e;
        }
    }
};

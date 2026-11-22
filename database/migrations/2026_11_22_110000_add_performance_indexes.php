<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona índices de performance nas tabelas mais consultadas.
     *
     * Objetivos:
     * - Índices em colunas de filtro frequentes (user_id, status1, status2, ano).
     * - FULLTEXT para busca por nome do filme e sinopse (apenas MySQL).
     * - Índices em tabelas pivot e foreign keys.
     * - FULLTEXT na tabela users para busca no UserManager (apenas MySQL).
     */
    public function up(): void
    {
        // Índices na tabela posts (funcionam em SQLite e MySQL)
        Schema::table('posts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('status1');
            $table->index('status2');
            $table->index('ano');
        });

        // FULLTEXT apenas em MySQL — SQLite não suporta
        if (config('database.default') !== 'sqlite') {
            Schema::table('posts', function (Blueprint $table) {
                $table->fullText(['name', 'realname', 'sinopse']);
            });
        }

        // Índices na tabela pivot categorias_posts
        Schema::table('categorias_posts', function (Blueprint $table) {
            $table->index('categoria_id');
            $table->index('post_id');
        });

        // Índice na tabela movie_cast (foreign key post_id)
        Schema::table('movie_cast', function (Blueprint $table) {
            $table->index('post_id');
        });

        // FULLTEXT na tabela users apenas em MySQL
        if (config('database.default') !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->fullText(['name', 'email']);
            });
        }
    }

    /**
     * Remove os índices adicionados.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['status1']);
            $table->dropIndex(['status2']);
            $table->dropIndex(['ano']);
        });

        // FULLTEXT apenas em MySQL
        if (config('database.default') !== 'sqlite') {
            Schema::table('posts', function (Blueprint $table) {
                $table->dropFullText(['name', 'realname', 'sinopse']);
            });
        }

        Schema::table('categorias_posts', function (Blueprint $table) {
            $table->dropIndex(['categoria_id']);
            $table->dropIndex(['post_id']);
        });

        Schema::table('movie_cast', function (Blueprint $table) {
            $table->dropIndex(['post_id']);
        });

        // FULLTEXT apenas em MySQL
        if (config('database.default') !== 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropFullText(['name', 'email']);
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona a coluna tmdb_id na tabela posts para identificar filmes únicos da TMDB.
     * Isso permite o seed incremental: verificar se o filme já existe antes de criar.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->unsignedInteger('tmdb_id')->nullable()->after('id');
            $table->unique('tmdb_id');
        });
    }

    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropUnique('posts_tmdb_id_unique');
            $table->dropColumn('tmdb_id');
        });
    }
};

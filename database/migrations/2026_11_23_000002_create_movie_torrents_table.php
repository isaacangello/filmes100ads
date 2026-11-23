<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cria a tabela movie_torrents para armazenar múltiplos torrents por filme.
     * Cada filme pode ter vários torrents com diferentes qualidades (720p, 1080p, 4K).
     */
    public function up(): void
    {
        Schema::create('movie_torrents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('post_id')->constrained('posts')->onDelete('cascade');
            $table->string('title'); // Título do torrent
            $table->text('magnet'); // Magnet link
            $table->string('size')->nullable(); // Tamanho ex: "1.2 GB"
            $table->string('quality', 20)->nullable(); // Qualidade detectada: 720p, 1080p, 4K, etc.
            $table->unsignedInteger('seeders')->nullable();
            $table->unsignedInteger('leechers')->nullable();
            $table->string('source', 50)->nullable(); // Fonte: BitSearch, Baixetorrent, etc.
            $table->timestamps();

            $table->index('post_id');
            $table->index('quality');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movie_torrents');
    }
};

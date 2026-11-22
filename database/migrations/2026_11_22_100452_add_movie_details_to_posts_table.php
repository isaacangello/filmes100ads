<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->string('vote_average')->nullable()->after('poster_url');
            $table->integer('vote_count')->nullable()->after('vote_average');
            $table->string('original_language')->nullable()->after('vote_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn(['vote_average', 'vote_count', 'original_language']);
        });
    }
};

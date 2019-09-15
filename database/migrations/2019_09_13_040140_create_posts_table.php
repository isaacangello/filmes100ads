<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id');
            $table->string('name');
            $table->string('realname')->nullable();
            $table->string('diretor')->nullable();
            $table->string('pais')->nullable();
            $table->string('descricao')->nullable();
            $table->text('sinopse')->nullable();
            $table->string('duracao')->nullable();
            $table->string('ano')->nullable();
            $table->string('status1')->default('ativo');;
            $table->string('status2')->default('aprovado');;
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}

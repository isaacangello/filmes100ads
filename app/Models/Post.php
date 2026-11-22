<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    protected $table = 'posts';

    protected $fillable = [
        'user_id', 'name', 'realname', 'sinopse', 'descricao',
        'diretor', 'duracao', 'ano', 'pais', 'status1', 'status2',
        'magnet_link', 'poster_url', 'vote_average', 'vote_count', 'original_language',
    ];

    /**
     * Usuário que cadastrou o filme.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Categorias do filme.
     */
    public function categorias()
    {
        return $this->belongsToMany(Categoria::class, 'categorias_posts', 'post_id', 'categoria_id');
    }

    /**
     * Elenco do filme.
     */
    public function cast()
    {
        return $this->hasMany(MovieCast::class, 'post_id')->orderBy('order');
    }
}

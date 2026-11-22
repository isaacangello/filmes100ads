<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Registro de visualização de filme.
 */
class MovieView extends Model
{
    protected $table = 'movie_views';

    public $timestamps = false;

    protected $fillable = [
        'post_id',
        'user_id',
        'ip_hash',
    ];

    /**
     * Filme visualizado.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * Usuário que visualizou (se logado).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

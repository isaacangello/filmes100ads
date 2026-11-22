<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovieCast extends Model
{
    protected $table = 'movie_cast';

    protected $fillable = [
        'post_id', 'name', 'character', 'profile_path', 'order',
    ];

    /**
     * Filme associado.
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}

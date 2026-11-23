<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieTorrent extends Model
{
    protected $table = 'movie_torrents';

    protected $fillable = [
        'post_id', 'title', 'magnet', 'size', 'quality', 'seeders', 'leechers', 'source',
    ];

    /**
     * Filme associado.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}

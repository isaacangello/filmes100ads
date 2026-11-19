<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //
    protected $table = 'posts';
    protected $fillable = ['name','sinopse','realname','diretor','duracao','pais','status1','status2'];


}

<?php

namespace app;

use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    //
    protected $table = 'categorias';
    protected $fillable = ['id','name','detalhes','status1'];
}

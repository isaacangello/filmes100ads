<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Urls extends Model
{
    //
    protected $fillable = ['post_id','label','url'];
}

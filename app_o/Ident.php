<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ident extends Model
{
    //
    protected $table = 'ident';
    protected $guarded = [];
    public $timestamps = false;
}

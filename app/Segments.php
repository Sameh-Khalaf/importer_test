<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Segments extends Model
{
    //
    protected $table = 'segments';
    protected $guarded = [];
    public $timestamps = false;
}

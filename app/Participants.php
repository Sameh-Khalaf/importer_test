<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Participants extends Model
{
    //
    protected $table = 'participants';
    protected $guarded = ['number','name','id'];
    public $timestamps = false;
}

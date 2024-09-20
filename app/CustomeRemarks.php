<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomeRemarks extends Model
{
    //
    protected $table = 'custom_remarks';
    protected $guarded = [];
}

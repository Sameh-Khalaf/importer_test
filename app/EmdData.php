<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmdData extends Model
{
    protected $table = 'emd_data';
    protected $guarded = ['tsm_identifier'];
    public $timestamps = false;
}

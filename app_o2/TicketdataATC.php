<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketdataATC extends Model
{
    protected $table = 'ticketdata_atc';
    protected $guarded = [];
    public $timestamps = false;
}

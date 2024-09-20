<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketdataTaxes extends Model
{
    protected $table = 'ticketdata_taxes';
    protected $guarded = [];
    public $timestamps = false;
}

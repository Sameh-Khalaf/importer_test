<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InvoiceRemarks extends Model
{
    //
    protected $table = 'invoice_remarks';
    public $timestamps = false;
    protected $guarded = [];
}

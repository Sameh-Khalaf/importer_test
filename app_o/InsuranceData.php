<?php

namespace App;

use App\Lib\Amadeus\Flight\Collections\IdentCollection;
use App\Lib\Amadeus\Flight\Collections\TicketDataCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class InsuranceData extends Model
{
    //
    protected $table = 'insurance_data';
    protected $guarded = ['idnetifier'];
    public $timestamps = false;
}
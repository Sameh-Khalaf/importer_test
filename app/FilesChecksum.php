<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FilesChecksum extends Model
{
    //
    protected $table = 'files_checksum';
    protected $guarded = [];
//    public $timestamps = false;
}

<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProcessedFiles extends Model
{
    //
    protected $table = 'processed_files';
    public $timestamps = false;
}

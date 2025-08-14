<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemUpdate extends Model
{
    protected $fillable = [
        'zip_name','backup_code_path','backup_db_path','status','log','user_id'
    ];
}

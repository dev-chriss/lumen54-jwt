<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{
    protected $guarded = ['id'];

    protected $dates = ['created_at', 'updated_at'];

    protected $hidden = ['deleted_at', 'extra'];
}

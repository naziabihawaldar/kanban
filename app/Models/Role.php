<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laratrust\Models\LaratrustRole;

class Role extends LaratrustRole
{
    use HasFactory;
    public function user()
    {
    	return $this->belongsTo('App\User');
    }
}

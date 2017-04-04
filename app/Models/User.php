<?php

namespace App\Models;

use Illuminate\Auth\Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

class User extends BaseModel implements AuthenticatableContract, JWTSubject
{
    use SoftDeletes, Authenticatable;

    protected $hidden = ['password', 'deleted_at'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // jwt Need to implement the method
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    // jwt Need to implement the method
    public function getJWTCustomClaims()
    {
        return [];
    }
}

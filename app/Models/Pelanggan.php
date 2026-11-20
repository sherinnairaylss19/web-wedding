<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Pelanggan extends Authenticatable
{
    protected $fillable = ['nama', 'email', 'password'];
    protected $hidden = ['password', 'remember_token'];
}
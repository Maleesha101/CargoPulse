<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'department'
    ];

    protected $hidden = [
        'password_hash'
    ];

    protected $casts = [
        'password_hash' => 'hashed'
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isOperations()
    {
        return in_array($this->role, ['operations', 'admin']);
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }
}
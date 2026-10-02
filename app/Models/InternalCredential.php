<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InternalCredential extends Model
{
    protected $table = 'internal_credentials';

    protected $fillable = [
        'service_name',
        'username',
        'secret_value',
        'environment',
        'description'
    ];

    protected $hidden = ['secret_value'];
}
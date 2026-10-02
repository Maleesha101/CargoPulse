<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SecurityChallenge extends Model
{
    protected $table = 'security_challenges';

    protected $fillable = [
        'challenge_name',
        'flag',
        'description'
    ];
}
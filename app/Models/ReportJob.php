<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportJob extends Model
{
    protected $fillable = [
        'user_id',
        'report_type',
        'filter_expression',
        'status',
        'result_reference'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getFilterExpression()
    {
        return $this->filter_expression;
    }
}
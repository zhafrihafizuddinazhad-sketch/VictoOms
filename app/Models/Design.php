<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Design extends Model
{
    protected $fillable = [
        'order_id',
        'designer_id',
        'design_file',
        'remarks',
        'completed_at'
    ];

    public function order()
{
    return $this->belongsTo(Order::class);
}

public function designer()
{
    return $this->belongsTo(User::class, 'designer_id');
}
}

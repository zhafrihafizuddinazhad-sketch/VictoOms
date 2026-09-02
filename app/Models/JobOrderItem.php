<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOrderItem extends Model
{
    protected $fillable = [
    'job_order_id',
    'item_name',
    'size',
    'quantity',
    'name',
    'number',
];


    public function jobOrder()
    {
        return $this->belongsTo(
            JobOrder::class
        );
    }
}
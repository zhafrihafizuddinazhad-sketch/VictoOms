<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOrder extends Model
{
    protected $fillable = [

        'order_id',
        'job_order_no',
        'created_by',
        'status',
        'file_path',
        'file_name',
        'generated_at',
        'submitted_at',
        'remarks',
        'image_path',
        'image_name',

    ];


    public function order()
    {
        return $this->belongsTo(
            Order::class
        );
    }


    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }


    public function items()
    {
        return $this->hasMany(
            JobOrderItem::class
        );
    }

    public function images()
{
    return $this->hasMany(
        JobOrderImage::class
    );
}
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobOrderImage extends Model
{
    protected $fillable = [

        'job_order_id',

        'image_name',

        'image_path',

    ];


    public function jobOrder()
    {
        return $this->belongsTo(
            JobOrder::class
        );
    }
}
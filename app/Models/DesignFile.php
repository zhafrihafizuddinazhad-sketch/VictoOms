<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesignFile extends Model
{
    protected $fillable = [
        'order_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_extension',
        'version',
        'remarks',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SamplePhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_order_id',
        'photo_type',
        'uploaded_by_user_id',
        'uploaded_by_customer',
        'file_path',
        'notes',
    ];

    protected $casts = [
        'uploaded_by_customer' => 'boolean',
    ];

    public function sampleOrder(): BelongsTo
    {
        return $this->belongsTo(SampleOrder::class);
    }

    public function uploadedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }
}
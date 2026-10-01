<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_order_id',
        'returned_at',
        'received_by',
        'condition',
        'damage_description',
        'deposit_action',
        'notes',
    ];

    protected $casts = [
        'returned_at' => 'datetime',
    ];

    public function sampleOrder(): BelongsTo
    {
        return $this->belongsTo(SampleOrder::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }
}
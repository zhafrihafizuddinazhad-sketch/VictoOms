<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_order_id',
        'sample_id',
        'item_type',
        'sample_name',
        'quantity',
        'fabric',
        'description',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    public function sampleOrder(): BelongsTo
    {
        return $this->belongsTo(SampleOrder::class);
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class);
    }
}

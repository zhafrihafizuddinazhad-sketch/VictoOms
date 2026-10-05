<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SampleOrderEvent extends Model
{
    protected $fillable = [
        'event_key',
        'user_id',
    ];

    public function sampleOrder(): BelongsTo
    {
        return $this->belongsTo(SampleOrder::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sample extends Model
{
    use HasFactory;

    protected $fillable = [
        'sample_code',
        'item_type',
        'fabric',
        'size',
        'colour',
        'status',
        'notes',
    ];

    public function sampleItems(): HasMany
    {
        return $this->hasMany(SampleItem::class);
    }
}
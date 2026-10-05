<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SampleOrder extends Model
{
    use HasFactory;

    protected $fillable = [
    'customer_name',
    'order_number',
    'collection_method',
    'pickup_date',
    'delivery_date',
    'delivery_address',
    'return_date',
    'deposit_amount',
    'deposit_status',
    'status',
    'customer_token',
    'notes',
    'customer_id',
    'created_source',
];

    protected $casts = [
        'pickup_date' => 'date',
        'delivery_date' => 'date',
        'return_date' => 'date',
        'deposit_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function sampleItems(): HasMany
    {
        return $this->hasMany(SampleItem::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(SamplePhoto::class);
    }

    public function sampleReturn(): HasOne
    {
        return $this->hasOne(SampleReturn::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(SampleOrderEvent::class);
    }

    public function depositIsSatisfied(): bool
    {
        if ($this->created_source === 'customer') {
            return $this->deposit_status === 'paid';
        }

        return (float) $this->deposit_amount <= 0 || $this->deposit_status === 'paid';
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'customer_name',
        'phone',
        'email',
        'company',
        'address',
        'state',
        'postcode',
        'remarks',
    ];

    public function orders()
{
    return $this->hasMany(Order::class);
}

    public function sampleOrders(): HasMany
    {
        return $this->hasMany(SampleOrder::class);
    }
}

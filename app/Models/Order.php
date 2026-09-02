<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
    'customer_id',
    'designer_id',
    'cameraman_id',
    'order_no',
    'due_date',
    'status',
    'remarks',
    'delivery_method',
    'owner_review_comment',
    'reviewed_by',
    'reviewed_at',
    'customer_brief',
    'is_repeat_order',
    'repeat_from_order_id',
    'repeat_type',
];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function designs()
    {
    return $this->hasMany(Design::class);
    }

    public function designer()
    {
    return $this->belongsTo(User::class,'designer_id');
    }

    public function cameraman()
    {
    return $this->belongsTo(User::class, 'cameraman_id');
    }

    public function activityLogs()
{
    return $this->hasMany(ActivityLog::class)->latest();
}

public function designFiles()
{
    return $this->hasMany(DesignFile::class);
}

public function productPhotos()
{
    return $this->hasMany(ProductPhoto::class);
}

public function getStatusBadgeClass(): string
{
    return match ($this->status) {

        'Pending' => 'bg-warning text-dark',

        'Assigned' => 'bg-info',

        'In Progress' => 'bg-primary',

        'Pending Approval' => 'bg-secondary',

        'Printing' => 'bg-dark',

        'Ready at HQ' => 'bg-warning',

        'Photo Session' => 'bg-info',

        'Photo Completed' => 'bg-primary',

        'Out for Delivery' => 'bg-primary',

        'Waiting for Pickup' => 'bg-warning text-dark',

        'Completed' => 'bg-success',

        default => 'bg-secondary',

    };
}

public function getStatusBadgeText(): string
{
    return $this->status;
}

public function references()
{
    return $this->hasMany(OrderReference::class);
}

public function originalOrder()
{
    return $this->belongsTo(
        Order::class,
        'repeat_from_order_id'
    );
}

public function repeatOrders()
{
    return $this->hasMany(
        Order::class,
        'repeat_from_order_id'
    );
}

public function jobOrders()
{
    return $this->hasMany(
        JobOrder::class
    );
}

public function latestJobOrder()
{
    return $this->hasOne(
        JobOrder::class
    )->latestOfMany();
}
}
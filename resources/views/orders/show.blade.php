@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <h1>Order Details</h1>

    <a href="{{ auth()->user()->hasRole('admin')
    ? route('admin.orders.index')
    : route('orders.index') }}"
    class="btn btn-secondary">

    <i class="fas fa-arrow-left mr-1"></i>
    Back

</a>

</div>

@include('orders.partials.order-info')

@include('orders.partials.order-timeline')

@include('orders.partials.customer-assets')

@include('orders.partials.products')

@include('orders.partials.repeat-info')

@include('orders.partials.uploaded-designs')

@include('orders.partials.job-orders')

@include('orders.partials.product-photos')

@include('orders.partials.actions')

@include('orders.partials.image-preview')

@endsection
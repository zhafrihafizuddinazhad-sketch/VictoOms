@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">Add Sample Item</h1><p class="text-muted mb-0">Add an item to <strong>{{ $sampleOrder->order_number }}</strong>.</p></div>
        <a href="{{ route('sample-orders.show', $sampleOrder) }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
    @include('sample-items._form', ['action' => route('sample-items.store', $sampleOrder), 'httpMethod' => 'POST', 'submitLabel' => 'Add Sample Item'])
</div>
@endsection

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">Edit Sample Item</h1><p class="text-muted mb-0">Update an item on <strong>{{ $sampleItem->sampleOrder->order_number }}</strong>.</p></div>
        <a href="{{ route('sample-orders.show', $sampleItem->sampleOrder) }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
    @include('sample-items._form', ['sampleOrder' => $sampleItem->sampleOrder, 'action' => route('sample-items.update', $sampleItem), 'httpMethod' => 'PUT', 'submitLabel' => 'Save Changes'])
</div>
@endsection

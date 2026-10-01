@extends('layouts.app')

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Sample Orders</h1>
            <p class="text-muted mb-0">
                Manage customer sample orders.
            </p>
        </div>

        <a href="{{ route('sample-orders.create') }}" class="btn btn-primary">
            + New Sample Order
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        @foreach(['total' => 'Total orders', 'pending_payment' => 'Pending payment', 'upcoming_returns' => 'Returns in 7 days', 'overdue_returns' => 'Overdue returns', 'office' => 'Office orders', 'lalamove' => 'Lalamove orders', 'active' => 'Active orders', 'completed' => 'Completed'] as $key => $label)
            <div class="col-6 col-xl-3">
                <div class="card h-100"><div class="card-body">
                    <div class="small text-muted">{{ $label }}</div>
                    <div class="fs-4 fw-semibold">{{ $summary[$key] }}</div>
                </div></div>
            </div>
        @endforeach
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Sample Order List</strong>
        </div>

        <div class="card-body">

            <form method="GET" action="{{ route('sample-orders.index') }}" class="row g-2 align-items-end mb-4">
                <div class="col-md-3"><label class="form-label" for="filter-search">Search</label><input id="filter-search" name="search" value="{{ request('search') }}" class="form-control" placeholder="Order number or customer"></div>
                <div class="col-md-2"><label class="form-label" for="filter-status">Status</label><select id="filter-status" name="status" class="form-select"><option value="">All statuses</option>@foreach(['pending','pending_payment','ready_for_collection','collected','in_transit','received','return_pending','returned','completed','cancelled'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>@endforeach</select></div>
                <div class="col-md-2"><label class="form-label" for="filter-collection">Collection</label><select id="filter-collection" name="collection_method" class="form-select"><option value="">All methods</option><option value="office" @selected(request('collection_method') === 'office')>Office pickup</option><option value="lalamove" @selected(request('collection_method') === 'lalamove')>Lalamove</option></select></div>
                <div class="col-md-2"><label class="form-label" for="filter-pickup">Pickup date</label><input id="filter-pickup" type="date" name="pickup_date" value="{{ request('pickup_date') }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label" for="filter-return">Return date</label><input id="filter-return" type="date" name="return_date" value="{{ request('return_date') }}" class="form-control"></div>
                <div class="col-md-2"><label class="form-label" for="filter-due">Return timing</label><select id="filter-due" name="return_due" class="form-select"><option value="">Any</option><option value="upcoming" @selected(request('return_due') === 'upcoming')>Due in 7 days</option><option value="overdue" @selected(request('return_due') === 'overdue')>Overdue</option></select></div>
                <div class="col-md-1 d-flex gap-2"><button class="btn btn-primary" type="submit">Filter</button></div>
                <div class="col-12"><a href="{{ route('sample-orders.index') }}" class="small">Clear filters</a></div>
            </form>

            @if($orders->count() > 0)

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Customer</th>
                                <th>Collection</th>
                                <th>Pickup Date</th>
                                <th>Return Date</th>
                                <th>Deposit</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($orders as $order)
                                <tr>

                                    <td>
                                        <strong>
                                            {{ $order->order_number }}
                                        </strong>
                                    </td>

                                    <td>
                                        {{ $order->customer_name ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $order->collection_method === 'lalamove' ? 'Lalamove' : 'Office pickup' }}
                                    </td>

                                    <td>
                                        {{ $order->pickup_date?->format('d M Y') ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $order->return_date?->format('d M Y') ?? '-' }}
                                    </td>

                                    <td>
                                        RM {{ number_format($order->deposit_amount, 2) }}
                                    </td>

                                    <td>
                                        <span class="badge {{ $order->status === 'completed' ? 'bg-success' : (in_array($order->status, ['cancelled'], true) ? 'bg-danger' : (in_array($order->status, ['pending','pending_payment'], true) ? 'bg-warning text-dark' : 'bg-primary')) }}">
                                            {{ ucwords(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </td>

                                    <td>
                                        <a
                                            href="{{ route('sample-orders.show', $order) }}"
                                            class="btn btn-sm btn-primary"
                                        >
                                            View
                                        </a>
                                    </td>

                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            @else

                <div class="text-center py-5">
                    <h5>No Sample Orders Found</h5>

                    <p class="text-muted mb-3">
                        There are currently no sample orders.
                    </p>

                    <a
                        href="{{ route('sample-orders.create') }}"
                        class="btn btn-primary"
                    >
                        Create Sample Order
                    </a>
                </div>

            @endif

            {{ $orders->links() }}

        </div>
    </div>

</div>
@endsection
```

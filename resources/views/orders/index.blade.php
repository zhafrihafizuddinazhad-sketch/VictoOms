@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <h1 class="mb-0">Orders</h1>

    <a href="{{ route('orders.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Create Order
    </a>

</div>

<form method="GET" action="{{ route('orders.index') }}" class="mb-3">

    <div class="row g-2 align-items-end">

        <div class="col-md-4">

            <label class="form-label">Search</label>

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                class="form-control"
                placeholder="Search Order No or Customer...">

        </div>

        <div class="col-md-3">

            <label class="form-label">Sort By</label>

            <select name="sort"
                    class="form-select"
                    onchange="this.form.submit()">

                <option value="created_at" {{ request('sort') == 'created_at' ? 'selected' : '' }}>
                    Newest
                </option>

                <option value="order_no" {{ request('sort') == 'order_no' ? 'selected' : '' }}>
                    Order No
                </option>

                <option value="customer" {{ request('sort') == 'customer' ? 'selected' : '' }}>
                    Customer
                </option>

                <option value="due_date" {{ request('sort') == 'due_date' ? 'selected' : '' }}>
                    Due Date
                </option>

                <option value="status" {{ request('sort') == 'status' ? 'selected' : '' }}>
                    Status
                </option>

            </select>

        </div>

        <div class="col-md-2">

            <label class="form-label">Direction</label>

            <select name="direction"
                    class="form-select"
                    onchange="this.form.submit()">

                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }}>
                    Ascending
                </option>

                <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }}>
                    Descending
                </option>

            </select>

        </div>

        <div class="col-md-3">

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-search"></i> Search
            </button>

            <a href="{{ route('orders.index') }}" class="btn btn-secondary">
                <i class="fas fa-undo"></i> Reset
            </a>

        </div>

    </div>

</form>

<div class="card">

    <div class="card-body">

        <table class="table table-bordered table-hover">

            <thead class="table-dark">
    <tr>

        <th>
            <a href="{{ route('orders.index', [
                'search' => request('search'),
                'sort' => 'order_no',
                'direction' => ($sort == 'order_no' && $direction == 'asc') ? 'desc' : 'asc'
            ]) }}" class="text-white text-decoration-none d-inline-flex align-items-center gap-1">
                Order No
                @if($sort == 'order_no')
                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                @else
                    <i class="fas fa-sort"></i>
                @endif
            </a>
        </th>

        <th>
            <a href="{{ route('orders.index', [
                'search' => request('search'),
                'sort' => 'customer',
                'direction' => ($sort == 'customer' && $direction == 'asc') ? 'desc' : 'asc'
            ]) }}" class="text-white text-decoration-none d-inline-flex align-items-center gap-1">
                Customer
                @if($sort == 'customer')
                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                @else
                    <i class="fas fa-sort"></i>
                @endif
            </a>
        </th>

        <th class="text-center">Items</th>

        <th class="text-end">Grand Total</th>

        <th>
            <a href="{{ route('orders.index', [
                'search' => request('search'),
                'sort' => 'due_date',
                'direction' => ($sort == 'due_date' && $direction == 'asc') ? 'desc' : 'asc'
            ]) }}" class="text-white text-decoration-none d-inline-flex align-items-center gap-1"">
                Due Date
                @if($sort == 'due_date')
                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                @else
                    <i class="fas fa-sort"></i>
                @endif
            </a>
        </th>

        <th>
            <a href="{{ route('orders.index', [
                'search' => request('search'),
                'sort' => 'status',
                'direction' => ($sort == 'status' && $direction == 'asc') ? 'desc' : 'asc'
            ]) }}" class="text-white text-decoration-none d-inline-flex align-items-center gap-1">
                Status
                @if($sort == 'status')
                    <i class="fas fa-sort-{{ $direction == 'asc' ? 'up' : 'down' }}"></i>
                @else
                    <i class="fas fa-sort"></i>
                @endif
            </a>
        </th>

        <th width="180">Action</th>

    </tr>
</thead>

            <tbody>

           @forelse($orders as $order)

<tr>

    <td>{{ $order->order_no }}</td>

    <td>{{ $order->customer->customer_name }}</td>

    <td class="text-center">
        {{ $order->items->count() }}
    </td>

    <td class="text-end">
        RM {{ number_format($order->items->sum('subtotal'), 2) }}
    </td>

    <td>
        {{ \Carbon\Carbon::parse($order->due_date)->format('d/m/Y') }}
    </td>

    <td>

       <span class="badge {{ $order->getStatusBadgeClass() }}">

    {{ $order->getStatusBadgeText() }}

</span>

@if($order->status == 'Ready at HQ')

    @if(!$order->cameraman_id)

        <br>

        <span class="badge bg-danger mt-1">

            <i class="fas fa-user-clock"></i>

            Need Cameraman

        </span>

    @else

        <br>

        <span class="badge bg-success mt-1">

            <i class="fas fa-camera"></i>

            {{ $order->cameraman->name }}

        </span>

    @endif

@endif
    </td>

    <td>

        <a
    href="{{ auth()->user()->hasRole('admin')
        ? route('admin.orders.show', $order)
        : route('orders.show', $order) }}"
    class="btn btn-info btn-sm">

    <i class="fas fa-eye mr-1"></i>
    View Order

</a>

        

        <a href="{{ route('orders.edit', $order) }}"
            class="btn btn-warning btn-sm">

            <i class="fas fa-edit"></i>

        </a>

        <form action="{{ route('orders.destroy', $order) }}"
              method="POST"
              class="d-inline">

            @csrf
            @method('DELETE')

            <button type="submit"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete this order?')">

                <i class="fas fa-trash"></i>

            </button>

        </form>

    </td>

</tr>

@empty

<tr>

    <td colspan="7" class="text-center">
        No order found.
    </td>

</tr>

@endforelse

            </tbody>

        </table>

        {{ $orders->links() }}

    </div>

</div>

@endsection
@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <h1 class="mb-1">

                <i class="fas fa-shopping-cart mr-2"></i>

                Order Management

            </h1>

            <p class="text-muted mb-0">

                Create, manage and monitor customer orders.

            </p>

        </div>


        <div>

            <a
                href="{{ route('admin.orders.create') }}"
                class="btn btn-primary"
            >

                <i class="fas fa-plus mr-1"></i>

                Create New Order

            </a>

        </div>

    </div>

</div>


{{-- ================================================= --}}
{{-- SEARCH & FILTER --}}
{{-- ================================================= --}}

<div class="card mt-3">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-filter mr-2"></i>

            Search & Filter

        </h5>

    </div>


    <div class="card-body">

        <form
            method="GET"
            action="{{ route('admin.orders.index') }}"
        >

            <div class="row">

                {{-- Search --}}

                <div class="col-md-5">

                    <label>

                        Search

                    </label>


                    <div class="input-group">

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Order number or customer name..."
                            value="{{ $search }}"
                        >


                        <div class="input-group-append">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                <i class="fas fa-search"></i>

                            </button>

                        </div>

                    </div>

                </div>


                {{-- Status --}}

                <div class="col-md-3">

                    <label>

                        Status

                    </label>


                    <select
                        name="status"
                        class="form-control"
                        onchange="this.form.submit()"
                    >

                        <option value="">

                            All Status

                        </option>


                        <option
                            value="Pending"
                            {{ $status == 'Pending' ? 'selected' : '' }}
                        >

                            Pending

                        </option>


                        <option
                            value="Assigned"
                            {{ $status == 'Assigned' ? 'selected' : '' }}
                        >

                            Assigned

                        </option>


                        <option
                            value="In Progress"
                            {{ $status == 'In Progress' ? 'selected' : '' }}
                        >

                            In Progress

                        </option>


                        <option
                            value="Pending Approval"
                            {{ $status == 'Pending Approval' ? 'selected' : '' }}
                        >

                            Pending Approval

                        </option>


                        <option
                            value="Printing"
                            {{ $status == 'Printing' ? 'selected' : '' }}
                        >

                            Printing

                        </option>


                        <option
                            value="Ready at HQ"
                            {{ $status == 'Ready at HQ' ? 'selected' : '' }}
                        >

                            Ready at HQ

                        </option>


                        <option
                            value="Photo Session"
                            {{ $status == 'Photo Session' ? 'selected' : '' }}
                        >

                            Photo Session

                        </option>


                        <option
                            value="Photo Completed"
                            {{ $status == 'Photo Completed' ? 'selected' : '' }}
                        >

                            Photo Completed

                        </option>


                        <option
                            value="Out for Delivery"
                            {{ $status == 'Out for Delivery' ? 'selected' : '' }}
                        >

                            Out for Delivery

                        </option>


                        <option
                            value="Waiting for Pickup"
                            {{ $status == 'Waiting for Pickup' ? 'selected' : '' }}
                        >

                            Waiting for Pickup

                        </option>


                        <option
                            value="Completed"
                            {{ $status == 'Completed' ? 'selected' : '' }}
                        >

                            Completed

                        </option>

                    </select>

                </div>


                {{-- Reset --}}

                <div class="col-md-2 d-flex align-items-end">

                    <a
                        href="{{ route('admin.orders.index') }}"
                        class="btn btn-secondary"
                    >

                        <i class="fas fa-redo mr-1"></i>

                        Reset

                    </a>

                </div>

            </div>

        </form>

    </div>

</div>


{{-- ================================================= --}}
{{-- ORDERS TABLE --}}
{{-- ================================================= --}}

<div class="card mt-3">

    <div class="card-header d-flex justify-content-between align-items-center">

        <div>

            <h5 class="mb-0">

                <i class="fas fa-list mr-2"></i>

                Orders

            </h5>

        </div>


        <span class="badge badge-primary">

            {{ $orders->total() }} Orders

        </span>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover table-bordered mb-0">

                <thead class="thead-light">

                    <tr>

                        <th>

                            Order

                        </th>


                        <th>

                            Customer

                        </th>


                        <th>

                            Products

                        </th>


                        <th>

                            Designer

                        </th>


                        <th>

                            Due Date

                        </th>


                        <th>

                            Status

                        </th>


                        <th
                            class="text-center"
                            style="width: 180px;"
                        >

                            Actions

                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($orders as $order)

                        <tr>

                            {{-- ========================================= --}}
                            {{-- ORDER --}}
                            {{-- ========================================= --}}

                            <td>

                                <strong>

                                    {{ $order->order_no }}

                                </strong>


                                <br>


                                <small class="text-muted">

                                    Created
                                    {{ $order->created_at->format('d M Y') }}

                                </small>

                            </td>


                            {{-- ========================================= --}}
                            {{-- CUSTOMER --}}
                            {{-- ========================================= --}}

                            <td>

                                @if($order->customer)

                                    <strong>

                                        {{ $order->customer->customer_name }}

                                    </strong>

                                @else

                                    <span class="text-muted">

                                        No Customer

                                    </span>

                                @endif

                            </td>


                            {{-- ========================================= --}}
                            {{-- PRODUCTS --}}
                            {{-- ========================================= --}}

                            <td>

                                @foreach($order->items->take(2) as $item)

                                    <div>

                                        {{ $item->product_name }}


                                        <small class="text-muted">

                                            × {{ $item->quantity }}

                                        </small>

                                    </div>

                                @endforeach


                                @if($order->items->count() > 2)

                                    <small class="text-muted">

                                        +
                                        {{ $order->items->count() - 2 }}
                                        more item(s)

                                    </small>

                                @endif

                            </td>


                            {{-- ========================================= --}}
                            {{-- DESIGNER --}}
                            {{-- ========================================= --}}

                            <td>

                                @if($order->designer)

                                    {{ $order->designer->name }}

                                @else

                                    <span class="badge badge-secondary">

                                        Unassigned

                                    </span>

                                @endif

                            </td>


                            {{-- ========================================= --}}
                            {{-- DUE DATE --}}
                            {{-- ========================================= --}}

                            <td>

                                @if($order->due_date)

                                    {{ \Carbon\Carbon::parse(
                                        $order->due_date
                                    )->format('d M Y') }}

                                @else

                                    <span class="text-muted">

                                        —

                                    </span>

                                @endif

                            </td>


                            {{-- ========================================= --}}
                            {{-- STATUS --}}
                            {{-- ========================================= --}}

                            <td>

                                @php

                                    $statusClass = match($order->status) {

                                        'Pending' =>
                                            'secondary',

                                        'Assigned' =>
                                            'info',

                                        'In Progress' =>
                                            'primary',

                                        'Pending Approval' =>
                                            'warning',

                                        'Printing' =>
                                            'dark',

                                        'Ready at HQ' =>
                                            'success',

                                        'Photo Session' =>
                                            'info',

                                        'Photo Completed' =>
                                            'success',

                                        'Out for Delivery' =>
                                            'primary',

                                        'Waiting for Pickup' =>
                                            'warning',

                                        'Completed' =>
                                            'success',

                                        default =>
                                            'secondary',

                                    };

                                @endphp


                                <span
                                    class="badge badge-{{ $statusClass }}"
                                >

                                    {{ $order->status }}

                                </span>

                            </td>


                            {{-- ========================================= --}}
                            {{-- ACTIONS --}}
                            {{-- ========================================= --}}

                            <td class="text-center">

                                <div
                                    class="btn-group"
                                    role="group"
                                >

                                    {{-- VIEW --}}

                                    <a
                                        href="{{ route(
                                            'orders.show',
                                            $order
                                        ) }}"
                                        class="btn btn-sm btn-info"
                                        title="View Order"
                                    >

                                        <i class="fas fa-eye"></i>

                                    </a>


                                    {{-- EDIT --}}

                                    <a
                                        href="{{ route(
                                            'admin.orders.edit',
                                            $order
                                        ) }}"
                                        class="btn btn-sm btn-warning"
                                        title="Edit Order"
                                    >

                                        <i class="fas fa-edit"></i>

                                    </a>


                                    {{-- DELETE --}}

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-danger"
                                        title="Delete Order"
                                        data-toggle="modal"
                                        data-target="#deleteOrderModal{{ $order->id }}"
                                    >

                                        <i class="fas fa-trash"></i>

                                    </button>

                                </div>

                            </td>

                        </tr>


                        {{-- ================================================= --}}
                        {{-- DELETE ORDER MODAL --}}
                        {{-- ================================================= --}}

                        <div
                            class="modal fade"
                            id="deleteOrderModal{{ $order->id }}"
                            tabindex="-1"
                            role="dialog"
                            aria-labelledby="deleteOrderModalLabel{{ $order->id }}"
                            aria-hidden="true"
                        >

                            <div
                                class="modal-dialog modal-dialog-centered"
                                role="document"
                            >

                                <div class="modal-content">


                                    {{-- MODAL HEADER --}}

                                    <div class="modal-header bg-danger text-white">

                                        <h5
                                            class="modal-title"
                                            id="deleteOrderModalLabel{{ $order->id }}"
                                        >

                                            <i class="fas fa-exclamation-triangle mr-2"></i>

                                            Delete Order

                                        </h5>


                                        <button
                                            type="button"
                                            class="close text-white"
                                            data-dismiss="modal"
                                            aria-label="Close"
                                        >

                                            <span aria-hidden="true">

                                                &times;

                                            </span>

                                        </button>

                                    </div>


                                    {{-- MODAL BODY --}}

                                    <div class="modal-body">

                                        <div class="text-center mb-4">

                                            <div
                                                class="mx-auto mb-3"
                                                style="
                                                    width: 70px;
                                                    height: 70px;
                                                    border-radius: 50%;
                                                    background-color: #f8d7da;
                                                    display: flex;
                                                    align-items: center;
                                                    justify-content: center;
                                                "
                                            >

                                                <i
                                                    class="fas fa-trash-alt text-danger"
                                                    style="font-size: 30px;"
                                                ></i>

                                            </div>


                                            <h4 class="font-weight-bold">

                                                Are you sure?

                                            </h4>


                                            <p class="text-muted mb-0">

                                                You are about to permanently
                                                delete this order.

                                            </p>

                                        </div>


                                        {{-- ORDER INFORMATION --}}

                                        <div class="card bg-light border-0">

                                            <div class="card-body">


                                                {{-- ORDER NUMBER --}}

                                                <div class="row">

                                                    <div class="col-5">

                                                        <strong>

                                                            Order No.

                                                        </strong>

                                                    </div>


                                                    <div class="col-7 text-right">

                                                        <span class="font-weight-bold">

                                                            {{ $order->order_no }}

                                                        </span>

                                                    </div>

                                                </div>


                                                <hr class="my-2">


                                                {{-- CUSTOMER --}}

                                                <div class="row">

                                                    <div class="col-5">

                                                        <strong>

                                                            Customer

                                                        </strong>

                                                    </div>


                                                    <div class="col-7 text-right">

                                                        {{ $order->customer->customer_name ?? 'N/A' }}

                                                    </div>

                                                </div>


                                                <hr class="my-2">


                                                {{-- DESIGNER --}}

                                                <div class="row">

                                                    <div class="col-5">

                                                        <strong>

                                                            Designer

                                                        </strong>

                                                    </div>


                                                    <div class="col-7 text-right">

                                                        {{ $order->designer->name ?? 'Unassigned' }}

                                                    </div>

                                                </div>


                                                <hr class="my-2">


                                                {{-- STATUS --}}

                                                <div class="row">

                                                    <div class="col-5">

                                                        <strong>

                                                            Status

                                                        </strong>

                                                    </div>


                                                    <div class="col-7 text-right">

                                                        <span
                                                            class="badge badge-warning"
                                                        >

                                                            {{ $order->status }}

                                                        </span>

                                                    </div>

                                                </div>


                                                <hr class="my-2">


                                                {{-- PRODUCT COUNT --}}

                                                <div class="row">

                                                    <div class="col-5">

                                                        <strong>

                                                            Products

                                                        </strong>

                                                    </div>


                                                    <div class="col-7 text-right">

                                                        {{ $order->items->count() }}

                                                        item(s)

                                                    </div>

                                                </div>

                                            </div>

                                        </div>


                                        {{-- WARNING --}}

                                        <div class="alert alert-warning mt-3 mb-0">

                                            <i class="fas fa-info-circle mr-1"></i>

                                            <strong>Warning:</strong>

                                            This action cannot be undone.

                                        </div>

                                    </div>


                                    {{-- MODAL FOOTER --}}

                                    <div class="modal-footer">

                                        <button
                                            type="button"
                                            class="btn btn-secondary"
                                            data-dismiss="modal"
                                        >

                                            <i class="fas fa-times mr-1"></i>

                                            Cancel

                                        </button>


                                        <form
                                            action="{{ route(
                                                'admin.orders.destroy',
                                                $order
                                            ) }}"
                                            method="POST"
                                            class="d-inline"
                                        >

                                            @csrf

                                            @method('DELETE')


                                            <button
                                                type="submit"
                                                class="btn btn-danger"
                                            >

                                                <i class="fas fa-trash-alt mr-1"></i>

                                                Delete Order

                                            </button>

                                        </form>

                                    </div>

                                </div>

                            </div>

                        </div>


                    @empty

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <i
                                    class="fas fa-shopping-cart fa-3x text-muted mb-3"
                                ></i>


                                <h5>

                                    No Orders Found

                                </h5>


                                <p class="text-muted mb-3">

                                    There are no orders matching your search
                                    or filter.

                                </p>


                                <a
                                    href="{{ route(
                                        'admin.orders.create'
                                    ) }}"
                                    class="btn btn-primary"
                                >

                                    <i class="fas fa-plus mr-1"></i>

                                    Create New Order

                                </a>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- PAGINATION --}}
    {{-- ================================================= --}}

    @if($orders->hasPages())

        <div class="card-footer">

            <div class="d-flex justify-content-center">

                {{ $orders->links() }}

            </div>

        </div>

    @endif

</div>

@endsection
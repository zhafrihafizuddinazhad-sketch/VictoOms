@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-redo"></i>

            Create Repeat Order

        </h1>

        <small class="text-muted">

            Create a new order based on a previous completed order.

        </small>

    </div>


    <a
        href="{{ route('orders.show', $order) }}"
        class="btn btn-secondary">

        <i class="fas fa-arrow-left"></i>

        Back to Order

    </a>

</div>


{{-- ================================================= --}}
{{-- ORIGINAL ORDER --}}
{{-- ================================================= --}}

<div class="card mb-3">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-history"></i>

            Original Order

        </h5>

    </div>


    <div class="card-body">

        <div class="row">

            <div class="col-md-4">

                <strong>Order No.</strong>

                <div>

                    {{ $order->order_no }}

                </div>

            </div>


            <div class="col-md-4">

                <strong>Customer</strong>

                <div>

                    {{ $order->customer->customer_name }}

                </div>

            </div>


            <div class="col-md-4">

                <strong>Status</strong>

                <div>

                    <span class="badge badge-success">

                        {{ $order->status }}

                    </span>

                </div>

            </div>

        </div>

    </div>

</div>


<form
    action="{{ route('orders.storeRepeat', $order) }}"
    method="POST">

    @csrf


    {{-- ================================================= --}}
    {{-- REPEAT TYPE --}}
    {{-- ================================================= --}}

    <div class="card mb-3">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-copy"></i>

                Repeat Type

            </h5>

        </div>


        <div class="card-body">


            <div class="custom-control custom-radio mb-3">

                <input
                    type="radio"
                    id="sameDesign"
                    name="repeat_type"
                    value="same_design"
                    class="custom-control-input"
                    checked>

                <label
                    class="custom-control-label"
                    for="sameDesign">

                    <strong>
                        Same Design — No Changes
                    </strong>

                    <br>

                    <small class="text-muted">

                        Reuse the previously approved design.
                        Designer does not need to redesign.

                    </small>

                </label>

            </div>


            <div class="custom-control custom-radio mb-3">

                <input
                    type="radio"
                    id="minorChanges"
                    name="repeat_type"
                    value="minor_changes"
                    class="custom-control-input">

                <label
                    class="custom-control-label"
                    for="minorChanges">

                    <strong>
                        Same Design — Minor Changes
                    </strong>

                    <br>

                    <small class="text-muted">

                        Use the previous design as a reference.
                        Designer needs to make changes.

                    </small>

                </label>

            </div>


        </div>


    {{-- ================================================= --}}
    {{-- ORDER INFORMATION --}}
    {{-- ================================================= --}}

    <div class="card mb-3">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-shopping-cart"></i>

                New Order Information

            </h5>

        </div>


        <div class="card-body">

            <div class="row">


                {{-- CUSTOMER --}}

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Customer

                    </label>

                    <input
                        type="text"
                        class="form-control"
                        value="{{ $order->customer->customer_name }}"
                        disabled>

                </div>


                {{-- DUE DATE --}}

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        New Due Date
                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="date"
                        name="due_date"
                        class="form-control"
                        value="{{ old('due_date') }}"
                        required>

                </div>


                {{-- DELIVERY --}}

                <div class="col-md-6 mb-3">

                    <label class="form-label">

                        Delivery Method
                        <span class="text-danger">*</span>

                    </label>

                    <select
                        name="delivery_method"
                        class="form-control"
                        required>

                        <option value="">
                            -- Select Delivery Method --
                        </option>

                        <option value="Delivery">

                            Delivery

                        </option>

                        <option value="Self Pickup">

                            Self Pickup

                        </option>

                    </select>

                </div>


                {{-- REMARKS --}}

                <div class="col-md-12 mb-3">

                    <label class="form-label">

                        Remarks

                    </label>

                    <textarea
                        name="remarks"
                        class="form-control"
                        rows="4"
                        placeholder="Optional remarks for this repeat order">{{ old('remarks') }}</textarea>

                </div>


            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- ORIGINAL ITEMS --}}
    {{-- ================================================= --}}

    <div class="card mb-3">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-box"></i>

                Original Order Items

            </h5>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered mb-0">

                    <thead>

                        <tr>

                            <th>
                                Product / Item
                            </th>

                            <th width="150">
                                Quantity
                            </th>

                            <th width="180">
                                Unit Price
                            </th>

                            <th width="180">
                                Subtotal
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach($order->items as $item)

                            <tr>

                                <td>

                                    {{ $item->product_name }}

                                </td>

                                <td>

                                    {{ $item->quantity }}

                                </td>

                                <td>

                                    RM
                                    {{ number_format($item->unit_price, 2) }}

                                </td>

                                <td>

                                    RM
                                    {{ number_format($item->subtotal, 2) }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- ACTION --}}
    {{-- ================================================= --}}

    <div class="d-flex justify-content-end mb-4">

        <a
            href="{{ route('orders.show', $order) }}"
            class="btn btn-secondary mr-2">

            Cancel

        </a>


        <button
            type="submit"
            class="btn btn-primary">

            <i class="fas fa-redo"></i>

            Create Repeat Order

        </button>

    </div>

</form>

@endsection
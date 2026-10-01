
@extends('layouts.app')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>
            <h1 class="h3 mb-1">Create Sample Order</h1>

            <p class="text-muted mb-0">
                Create a new sample order for a customer.
            </p>
        </div>

                <a href="{{ route('sample-orders.index') }}"
           class="btn btn-outline-secondary">
            Cancel
        </a>

    </div>


    {{-- Validation Errors --}}
    @if ($errors->any())

        <div class="alert alert-danger">

            <strong>Please check the following errors:</strong>

            <ul class="mb-0 mt-2">

                @foreach ($errors->all() as $error)

                    <li>{{ $error }}</li>

                @endforeach

            </ul>

        </div>

    @endif


    {{-- Sample Order Form --}}
    <form method="POST" action="{{ route('sample-orders.store') }}">

        @csrf


        {{-- Customer Information --}}
        <div class="card mb-4">

            <div class="card-header">
                <strong>Customer Information</strong>
            </div>

            <div class="card-body">

                <div class="mb-3">

                    <label for="customer_name" class="form-label">
                        Customer Name <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        class="form-control @error('customer_name') is-invalid @enderror"
                        id="customer_name"
                        name="customer_name"
                        value="{{ old('customer_name') }}"
                        placeholder="Enter customer name"
                        required
                    >

                    @error('customer_name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>

        </div>


        {{-- Collection Information --}}
        <div class="card mb-4">

            <div class="card-header">
                <strong>Collection Information</strong>
            </div>

            <div class="card-body">

                <div class="mb-3">

                    <label for="collection_method" class="form-label">
                        Collection Method <span class="text-danger">*</span>
                    </label>

                    <select
                        class="form-select @error('collection_method') is-invalid @enderror"
                        id="collection_method"
                        name="collection_method"
                        required
                    >

                        <option value="">
                            Select collection method
                        </option>

                        <option
                            value="office"
                            {{ old('collection_method') == 'office' ? 'selected' : '' }}
                        >
                            Office
                        </option>

                        <option
                            value="lalamove"
                            {{ old('collection_method') == 'lalamove' ? 'selected' : '' }}
                        >
                            Lalamove
                        </option>

                    </select>

                    @error('collection_method')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>


                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label for="pickup_date" class="form-label">
                            Pickup Date <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            class="form-control @error('pickup_date') is-invalid @enderror"
                            id="pickup_date"
                            name="pickup_date"
                            value="{{ old('pickup_date') }}"
                            max="{{ old('return_date') }}"
                            required
                        >

                        @error('pickup_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>


                    <div class="col-md-6 mb-3">

                        <label for="return_date" class="form-label">
                            Return Date <span class="text-danger">*</span>
                        </label>

                        <input
                            type="date"
                            class="form-control @error('return_date') is-invalid @enderror"
                            id="return_date"
                            name="return_date"
                            value="{{ old('return_date') }}"
                            min="{{ old('pickup_date') }}"
                            required
                        >

                        @error('return_date')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                </div>

            </div>

        </div>


        {{-- Payment Information --}}
        <div class="card mb-4">

            <div class="card-header">
                <strong>Payment Information</strong>
            </div>

            <div class="card-body">

                <div class="mb-3">

                    <label for="deposit_amount" class="form-label">
                        Deposit Amount (RM) <span class="text-danger">*</span>
                    </label>

                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        class="form-control @error('deposit_amount') is-invalid @enderror"
                        id="deposit_amount"
                        name="deposit_amount"
                        value="{{ old('deposit_amount') }}"
                        placeholder="0.00"
                        required
                    >

                    @error('deposit_amount')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>

        </div>


        {{-- Additional Information --}}
        <div class="card mb-4">

            <div class="card-header">
                <strong>Additional Information</strong>
            </div>

            <div class="card-body">

                <div class="mb-3">

                    <label for="notes" class="form-label">
                        Notes
                    </label>

                    <textarea
                        class="form-control @error('notes') is-invalid @enderror"
                        id="notes"
                        name="notes"
                        rows="4"
                        placeholder="Enter any additional notes..."
                    >{{ old('notes') }}</textarea>

                    @error('notes')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror

                </div>

            </div>

        </div>


        {{-- Form Actions --}}
        <div class="d-flex justify-content-end gap-2 mb-5">

            <a href="{{ route('sample-orders.index') }}"
               class="btn btn-secondary">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Create Sample Order
            </button>

        </div>

    </form>

</div>

@endsection
```

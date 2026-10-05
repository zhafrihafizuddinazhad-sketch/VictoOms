
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


    @error('order')<div class="alert alert-danger">{{ $message }}</div>@enderror

    {{-- Sample Order Form --}}
    <form method="POST" action="{{ route('sample-orders.store') }}" enctype="multipart/form-data" id="sample-order-form">

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

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center"><strong>Sample Items</strong><button type="button" class="btn btn-sm btn-outline-primary" id="add-sample-item">+ Add Sample Item</button></div>
            <div class="card-body" id="sample-items-list">
                @foreach(old('items', [['item_type' => 'shirt', 'quantity' => 1]]) as $index => $item)
                    <div class="row g-2 align-items-end sample-item-row mb-3">
                        <div class="col-sm-3"><label class="form-label">Item type</label><select name="items[{{ $index }}][item_type]" class="form-select" required><option value="shirt" @selected(($item['item_type'] ?? '') === 'shirt')>Shirt</option><option value="short" @selected(($item['item_type'] ?? '') === 'short')>Short</option></select></div>
                        <div class="col-sm-2"><label class="form-label">Quantity</label><input type="number" min="1" max="1000" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" class="form-control" required></div>
                        <div class="col-sm-3"><label class="form-label">Fabric</label><input name="items[{{ $index }}][fabric]" value="{{ $item['fabric'] ?? '' }}" class="form-control" maxlength="255"></div>
                        <div class="col-sm-3"><label class="form-label">Description</label><input name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" class="form-control" maxlength="5000"></div>
                        <div class="col-sm-1"><button type="button" class="btn btn-outline-danger remove-sample-item" aria-label="Remove item">Remove</button></div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><strong>Sample Photos</strong></div>
            <div class="card-body">
                <p class="small text-muted">Add photos of the original sample being provided. These are separate from handover and return evidence photos.</p>
                <label for="sample-photos" class="form-label">Choose photos <span class="text-muted">(optional, up to 10)</span></label>
                <input type="file" id="sample-photos" name="sample_photos[]" class="form-control @error('sample_photos.*') is-invalid @enderror" accept="image/*" capture="environment" multiple>
                <div class="form-text">JPG, PNG, or WebP · 5 MB maximum per photo.</div>
                @error('sample_photos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                <div id="sample-photo-preview" class="row g-2 mt-2" aria-live="polite"></div>
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

    <script>
        (() => {
            const list = document.getElementById('sample-items-list');
            let nextIndex = {{ count(old('items', [['item_type' => 'shirt', 'quantity' => 1]])) }};
            document.getElementById('add-sample-item').addEventListener('click', () => {
                const row = document.createElement('div');
                row.className = 'row g-2 align-items-end sample-item-row mb-3';
                row.innerHTML = `<div class="col-sm-3"><label class="form-label">Item type</label><select name="items[${nextIndex}][item_type]" class="form-select" required><option value="shirt">Shirt</option><option value="short">Short</option></select></div><div class="col-sm-2"><label class="form-label">Quantity</label><input type="number" min="1" max="1000" name="items[${nextIndex}][quantity]" value="1" class="form-control" required></div><div class="col-sm-3"><label class="form-label">Fabric</label><input name="items[${nextIndex}][fabric]" class="form-control" maxlength="255"></div><div class="col-sm-3"><label class="form-label">Description</label><input name="items[${nextIndex}][description]" class="form-control" maxlength="5000"></div><div class="col-sm-1"><button type="button" class="btn btn-outline-danger remove-sample-item" aria-label="Remove item">Remove</button></div>`;
                list.appendChild(row);
                nextIndex++;
            });
            list.addEventListener('click', (event) => {
                if (event.target.closest('.remove-sample-item') && list.querySelectorAll('.sample-item-row').length > 1) {
                    event.target.closest('.sample-item-row').remove();
                }
            });
            const input = document.getElementById('sample-photos');
            const preview = document.getElementById('sample-photo-preview');
            input.addEventListener('change', () => {
                preview.replaceChildren();
                Array.from(input.files || []).slice(0, 10).forEach((file) => {
                    if (!file.type.startsWith('image/')) return;
                    const column = document.createElement('div');
                    column.className = 'col-4 col-sm-3 col-md-2';
                    const image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = 'Selected sample photo preview';
                    image.className = 'img-fluid rounded border';
                    image.style.height = '100px';
                    image.style.width = '100%';
                    image.style.objectFit = 'cover';
                    column.appendChild(image);
                    preview.appendChild(column);
                });
            });
        })();
    </script>

</div>

@endsection
```

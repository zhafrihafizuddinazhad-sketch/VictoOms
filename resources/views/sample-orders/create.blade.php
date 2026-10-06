@extends('layouts.admin')

@section('content')
<div class="container-fluid pb-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div><h1 class="h3 mb-1">Create Sample Order</h1><p class="text-muted mb-0">Enter the customer’s request and collection details.</p></div>
        <a href="{{ route('sample-orders.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
    @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Please check the following errors:</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @error('order')<div class="alert alert-danger">{{ $message }}</div>@enderror
    <form method="POST" action="{{ route('sample-orders.store') }}" enctype="multipart/form-data" id="sample-order-form">
        @csrf
        @include('sample-orders.partials.request-fields')

        <div class="sample-request-fields">
            <section class="card request-card">
                <div class="card-header"><span class="section-kicker">05 · Staff details</span><strong>Deposit and original photos</strong></div>
                <div class="card-body">
                    <div class="mb-4"><label for="deposit_amount" class="form-label">Deposit amount (RM) <span class="text-danger">*</span></label><input type="number" step="0.01" min="0" class="form-control @error('deposit_amount') is-invalid @enderror" id="deposit_amount" name="deposit_amount" value="{{ old('deposit_amount') }}" placeholder="0.00" required>@error('deposit_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">The order remains unpaid until a payment is recorded.</div></div>
                    <p class="small text-muted">Original photos are separate from handover, delivery, and return evidence.</p>
                    <p class="form-label mb-2">Original sample photos <span class="text-muted">(optional, up to 10)</span></p>
                    @include('sample-orders.partials.photo-upload', [
                        'photoUploadId' => 'original-sample-photos',
                        'photoUploadName' => 'sample_photos',
                        'photoUploadLimit' => 10,
                        'photoUploadRequired' => false,
                        'photoUploadHelp' => 'JPG, PNG, or WebP · 5 MB maximum per photo.',
                    ])
                    @error('sample_photos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </section>
            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-4"><a href="{{ route('sample-orders.index') }}" class="btn btn-outline-secondary">Cancel</a><button type="submit" class="btn btn-primary btn-lg px-4">Create Sample Order</button></div>
        </div>
    </form>
</div>
@endsection

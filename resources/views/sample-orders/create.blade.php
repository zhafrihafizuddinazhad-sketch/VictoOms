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
                    <label for="sample-photos" class="form-label">Original sample photos <span class="text-muted">(optional, up to 10)</span></label>
                    <input type="file" id="sample-photos" name="sample_photos[]" class="form-control @error('sample_photos.*') is-invalid @enderror" accept="image/*" capture="environment" multiple>
                    <div class="form-text">JPG, PNG, or WebP · 5 MB maximum per photo.</div>
                    @error('sample_photos.*')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    <div id="sample-photo-preview" class="row g-2 mt-2" aria-live="polite"></div>
                </div>
            </section>
            <div class="d-flex flex-column flex-sm-row justify-content-end gap-2 mb-4"><a href="{{ route('sample-orders.index') }}" class="btn btn-outline-secondary">Cancel</a><button type="submit" class="btn btn-primary btn-lg px-4">Create Sample Order</button></div>
        </div>
    </form>
</div>
<script>
(() => { const input=document.getElementById('sample-photos'); const preview=document.getElementById('sample-photo-preview'); if(!input||!preview)return; input.addEventListener('change',()=>{ preview.replaceChildren(); [...input.files].slice(0,10).forEach(file=>{ if(!file.type.startsWith('image/'))return; const column=document.createElement('div'); column.className='col-4 col-sm-3 col-md-2'; const image=document.createElement('img'); image.src=URL.createObjectURL(file); image.alt=file.name; image.className='img-fluid rounded'; image.style.aspectRatio='1'; image.style.objectFit='cover'; column.append(image); preview.append(column); }); }); })();
</script>
@endsection

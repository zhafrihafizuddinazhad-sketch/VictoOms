@extends('layouts.customer')

@section('content')
    <div class="card shadow-sm border-0" style="border-radius:18px">
        <div class="card-body p-4 p-md-5">
            <h1 class="h4 mb-1">Sample Photo Update</h1>
            <p class="text-muted mb-4">Order <strong>{{ $sampleOrder->order_number }}</strong></p>

            @if(session('success'))<div class="alert alert-success" role="status">{{ session('success') }}</div>@endif
            @if($errors->any())<div class="alert alert-danger" role="alert"><strong>Please check your upload.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

            <p class="mb-3">{{ $customerMessage }}</p>
            @if($photoPurpose)
                <form action="{{ route('customer-sample-photos.store', $customerToken) }}" method="POST" enctype="multipart/form-data" id="customer-photo-form">
                    @csrf
                    <input type="hidden" name="photo_type" value="{{ $photoPurpose }}">

                    <div class="mb-3">
                        <label for="customer-photos" class="form-label font-weight-bold">Take or choose a photo</label>
                        <input type="file" name="photos[]" id="customer-photos" class="form-control-file d-block w-100 border rounded p-2" accept="image/*" capture="environment" multiple required>
                        <small class="form-text text-muted">JPG, PNG, or WebP · up to 5 photos · 5 MB each</small>
                    </div>
                    <div id="photo-preview" class="row mb-3" aria-live="polite"></div>

                    <div class="mb-4">
                        <label for="photo-notes" class="form-label">Note <span class="text-muted">(optional)</span></label>
                        <input type="text" name="notes" id="photo-notes" class="form-control" maxlength="500" value="{{ old('notes') }}" placeholder="Add a short note if needed">
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" id="submit-photos">Submit Photo</button>
                </form>
            @endif
        </div>
    </div>

    <script>
        (() => {
            const input = document.getElementById('customer-photos');
            const preview = document.getElementById('photo-preview');
            const form = document.getElementById('customer-photo-form');
            if (!input || !form) return;
            const button = document.getElementById('submit-photos');
            input.addEventListener('change', () => {
                preview.replaceChildren();
                Array.from(input.files || []).slice(0, 5).forEach((file) => {
                    if (!file.type.startsWith('image/')) return;
                    const col = document.createElement('div');
                    col.className = 'col-4 mb-2';
                    const image = document.createElement('img');
                    image.src = URL.createObjectURL(file);
                    image.alt = 'Selected photo preview';
                    image.className = 'img-fluid rounded border';
                    image.style.height = '100px';
                    image.style.width = '100%';
                    image.style.objectFit = 'cover';
                    col.appendChild(image);
                    preview.appendChild(col);
                });
            });
            form.addEventListener('submit', () => {
                button.disabled = true;
                button.textContent = 'Uploading…';
            });
        })();
    </script>
@endsection

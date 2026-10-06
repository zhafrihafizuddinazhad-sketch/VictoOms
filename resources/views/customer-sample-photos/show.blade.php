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
                        <p class="form-label font-weight-bold mb-2">Take or choose a photo</p>
                        @include('sample-orders.partials.photo-upload', [
                            'photoUploadId' => 'customer-token-photos',
                            'photoUploadName' => 'photos',
                            'photoUploadLimit' => 5,
                            'photoUploadRequired' => true,
                            'photoUploadHelp' => 'JPG, PNG, or WebP · up to 5 photos · 5 MB each.',
                        ])
                    </div>

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
            const form = document.getElementById('customer-photo-form');
            if (!form) return;
            const button = document.getElementById('submit-photos');
            form.addEventListener('submit', () => {
                const hasPreview = form.querySelector('[data-photo-uploader] [data-photo-preview] img');
                const hasUploadError = !form.querySelector('[data-photo-error]')?.classList.contains('d-none');
                if (!hasPreview || hasUploadError) return;
                button.disabled = true;
                button.textContent = 'Uploading…';
            });
        })();
    </script>
@endsection

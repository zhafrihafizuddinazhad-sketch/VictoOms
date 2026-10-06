@php
    $photoInputId = $photoUploadId;
    $photoInputName = $photoUploadName;
    $photoInputLimit = $photoUploadLimit;
@endphp

<div id="{{ $photoInputId }}" class="sample-photo-uploader" data-photo-uploader data-photo-limit="{{ $photoInputLimit }}" data-photo-name="{{ $photoInputName }}" data-photo-required="{{ $photoUploadRequired ? 'true' : 'false' }}">
    <div class="sample-photo-actions d-flex flex-column flex-sm-row">
        <button type="button" class="btn btn-primary" data-photo-camera>
            <i class="fas fa-camera mr-1" aria-hidden="true"></i> Take Photo
        </button>
        <button type="button" class="btn btn-outline-primary" data-photo-gallery>
            <i class="far fa-images mr-1" aria-hidden="true"></i> Choose from Gallery
        </button>
        <button type="button" class="btn btn-link text-muted d-none" data-photo-clear>
            Clear selected photos
        </button>
    </div>

    <p class="form-text text-muted mb-2">{{ $photoUploadHelp }}</p>
    <p class="small text-danger mb-2 d-none" data-photo-error role="alert"></p>
    <div class="row g-2" data-photo-preview aria-live="polite"></div>

    <div class="d-none" data-photo-camera-inputs></div>
    <div class="d-none" data-photo-gallery-inputs></div>
</div>

<script>
(() => {
    const uploader = document.getElementById(@json($photoInputId));
    if (!uploader) return;

    const limit = Number(uploader.dataset.photoLimit);
    const inputName = `${uploader.dataset.photoName}[]`;
    const cameraInputs = uploader.querySelector('[data-photo-camera-inputs]');
    const galleryInputs = uploader.querySelector('[data-photo-gallery-inputs]');
    const cameraButton = uploader.querySelector('[data-photo-camera]');
    const galleryButton = uploader.querySelector('[data-photo-gallery]');
    const clearButton = uploader.querySelector('[data-photo-clear]');
    const preview = uploader.querySelector('[data-photo-preview]');
    const error = uploader.querySelector('[data-photo-error]');
    let previewUrls = [];

    const selectedFiles = () => Array.from(uploader.querySelectorAll('input[type="file"]'))
        .flatMap((input) => Array.from(input.files || []));

    const showError = (message) => {
        error.textContent = message;
        error.classList.toggle('d-none', !message);
    };

    const renderPreview = () => {
        previewUrls.forEach((url) => URL.revokeObjectURL(url));
        previewUrls = [];
        preview.replaceChildren();
        selectedFiles().forEach((file) => {
            if (!file.type.startsWith('image/')) return;
            const column = document.createElement('div');
            column.className = 'col-6 col-sm-4 col-md-3 col-xl-2';
            const image = document.createElement('img');
            const previewUrl = URL.createObjectURL(file);
            previewUrls.push(previewUrl);
            image.src = previewUrl;
            image.alt = `Preview of ${file.name}`;
            image.className = 'sample-photo-preview-image rounded border';
            column.appendChild(image);
            preview.appendChild(column);
        });
        clearButton.classList.toggle('d-none', selectedFiles().length === 0);
        if (selectedFiles().length <= limit) showError('');
    };

    const openPicker = (source) => {
        const input = document.createElement('input');
        input.type = 'file';
        input.name = inputName;
        input.accept = 'image/*';
        input.multiple = source === 'gallery';
        input.className = 'd-none';
        input.setAttribute('aria-label', source === 'camera' ? 'Take a sample photo' : 'Choose sample photos from your gallery');
        if (source === 'camera') input.setAttribute('capture', 'environment');
        (source === 'camera' ? cameraInputs : galleryInputs).appendChild(input);

        input.addEventListener('change', () => {
            const picked = Array.from(input.files || []);
            if (picked.length === 0) {
                input.remove();
                return;
            }

            const currentCount = selectedFiles().length;
            if (currentCount > limit) {
                input.remove();
                renderPreview();
                showError(`You can upload up to ${limit} photos. Choose fewer photos or clear your selection.`);
                return;
            }

            renderPreview();
        }, { once: true });

        input.click();
    };

    cameraButton.addEventListener('click', () => openPicker('camera'));
    galleryButton.addEventListener('click', () => openPicker('gallery'));
    clearButton.addEventListener('click', () => {
        cameraInputs.replaceChildren();
        galleryInputs.replaceChildren();
        renderPreview();
        showError('');
        cameraButton.focus();
    });

    uploader.closest('form')?.addEventListener('submit', (event) => {
        const count = selectedFiles().length;
        if (count === 0 && uploader.dataset.photoRequired === 'true') {
            event.preventDefault();
            showError('Take a photo or choose one from your gallery before uploading.');
        } else if (count > limit) {
            event.preventDefault();
            showError(`You can upload up to ${limit} photos.`);
        } else {
            showError('');
        }
    });
})();
</script>

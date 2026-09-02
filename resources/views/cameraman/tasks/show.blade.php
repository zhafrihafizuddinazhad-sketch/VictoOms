@extends('layouts.admin')

@section('content')

<div class="content-header">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h1>Photo Session</h1>

            <a href="{{ route('cameraman.tasks') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>


        {{-- ========================================================= --}}
        {{-- ORDER INFORMATION --}}
        {{-- ========================================================= --}}

        <div class="card">

            <div class="card-header">
                <strong>Order Information</strong>
            </div>

            <div class="card-body">

                <div class="row">

                    <div class="col-md-3">

                        <strong>Order No</strong>

                        <p>
                            {{ $order->order_no }}
                        </p>

                    </div>


                    <div class="col-md-3">

                        <strong>Customer</strong>

                        <p>
                            {{ $order->customer->customer_name }}
                        </p>

                    </div>


                    <div class="col-md-3">

                        <strong>Delivery Method</strong>

                        <p>
                            {{ $order->delivery_method }}
                        </p>

                    </div>


                    <div class="col-md-3">

                        <strong>Status</strong>

                        <p>

                            @if($order->status == 'Photo Session')

                                <span class="badge bg-warning">
                                    {{ $order->status }}
                                </span>

                            @else

                                <span class="badge bg-success">
                                    {{ $order->status }}
                                </span>

                            @endif

                        </p>

                    </div>

                </div>

            </div>

        </div>



        {{-- ========================================================= --}}
        {{-- UPLOAD PRODUCT PHOTOS --}}
        {{-- ========================================================= --}}

        @if($order->status == 'Photo Session')

        <div class="card mt-3">

            <div class="card-header">

                <strong>
                    Upload Product Photos
                </strong>

            </div>


            <div class="card-body">

                <form
                    action="{{ route('cameraman.photos.store', $order) }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="photoUploadForm"
                >

                    @csrf


                    {{-- ================================================= --}}
                    {{-- PHOTO FILE INPUT --}}
                    {{-- ================================================= --}}

                    <div class="mb-3">

                        <label>
                            Select Photos
                        </label>


                        {{-- Real file input --}}
                        <input
                            type="file"
                            name="photos[]"
                            id="photo_files"
                            class="d-none"
                            multiple
                            accept=".jpg,.jpeg,.png,image/jpeg,image/png"
                        >


                        {{-- ================================================= --}}
                        {{-- DRAG & DROP AREA --}}
                        {{-- ================================================= --}}

                        <div
                            id="photoDropZone"
                            tabindex="0"
                            style="
                                border: 2px dashed #adb5bd;
                                border-radius: 8px;
                                padding: 35px 20px;
                                text-align: center;
                                cursor: pointer;
                                background: #f8f9fa;
                                transition: all 0.2s ease;
                            "
                        >

                            <div class="mb-2">

                                <i
                                    class="fas fa-cloud-upload-alt"
                                    style="
                                        font-size: 40px;
                                        color: #007bff;
                                    "
                                ></i>

                            </div>


                            <h5 class="mb-1">

                                Drag & Drop your photos here

                            </h5>


                            <p class="text-muted mb-2">

                                or

                                <span class="text-primary font-weight-bold">

                                    click to browse

                                </span>

                            </p>


                            <small class="text-muted">

                                You can also paste photos using
                                <strong>Ctrl + V</strong>

                            </small>


                            <div class="mt-2">

                                <small class="text-muted">

                                    Supported:
                                    JPG, JPEG, PNG

                                </small>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- SELECTED FILES --}}
                        {{-- ================================================= --}}

                        <div
                            id="selectedPhotoFiles"
                            class="mt-3"
                            style="display:none;"
                        ></div>

                    </div>



                    {{-- ================================================= --}}
                    {{-- REMARKS --}}
                    {{-- ================================================= --}}

                    <div class="mb-3">

                        <label>
                            Remarks
                        </label>

                        <textarea
                            name="remarks"
                            class="form-control"
                            rows="3"
                        ></textarea>

                    </div>



                    {{-- ================================================= --}}
                    {{-- SUBMIT --}}
                    {{-- ================================================= --}}

                    <button
                        type="submit"
                        class="btn btn-primary"
                        id="uploadPhotosButton"
                    >

                        <i class="fas fa-upload"></i>

                        Upload Photos

                    </button>

                </form>

            </div>

        </div>

        @endif



        {{-- ========================================================= --}}
        {{-- UPLOADED PHOTOS --}}
        {{-- ========================================================= --}}

        <div class="card mt-3">

            <div class="card-header">

                <strong>
                    Uploaded Photos
                </strong>

            </div>


            <div class="card-body">

                @if($productPhotos->isEmpty())

                    <div class="alert alert-secondary">

                        No photo uploaded yet.

                    </div>

                @else

                    <div class="row">

                        @foreach($productPhotos as $photo)

                            <div class="col-md-3 mb-4">

                                <div class="card shadow-sm h-100">

                                    <img
                                        src="{{ asset('storage/'.$photo->photo_path) }}"
                                        class="card-img-top preview-image"
                                        style="
                                            height:220px;
                                            object-fit:cover;
                                            cursor:pointer;
                                        "
                                        data-image="{{ asset('storage/'.$photo->photo_path) }}"
                                    >


                                    <div class="card-body">

                                        <small class="fw-bold d-block">

                                            {{ $photo->photo_name }}

                                        </small>


                                        @if($photo->remarks)

                                            <small class="text-muted">

                                                {{ $photo->remarks }}

                                            </small>

                                        @endif

                                    </div>


                                    @if($order->status == 'Photo Session')

                                    <div class="card-footer bg-white">

                                        <form
                                            action="{{ route('cameraman.photos.destroy', $photo) }}"
                                            method="POST"
                                        >

                                            @csrf

                                            @method('DELETE')


                                            <button
                                                class="btn btn-danger btn-sm w-100"
                                                onclick="return confirm('Delete this photo?')"
                                            >

                                                <i class="fas fa-trash"></i>

                                                Delete

                                            </button>

                                        </form>

                                    </div>

                                    @endif

                                </div>

                            </div>

                        @endforeach

                    </div>

                @endif

            </div>

        </div>



        {{-- ========================================================= --}}
        {{-- COMPLETE PHOTO SESSION --}}
        {{-- ========================================================= --}}

        @if($order->status == 'Photo Session')

        <div class="text-end mt-3">

            <form
                action="{{ route('cameraman.tasks.complete', $order) }}"
                method="POST"
            >

                @csrf

                @method('PATCH')


                <button
                    type="submit"
                    class="btn btn-success btn-lg"
                    {{ $productPhotos->count() == 0 ? 'disabled' : '' }}
                    onclick="return confirm('Complete this photo session?')"
                >

                    <i class="fas fa-check-circle"></i>

                    Complete Photo Session

                </button>

            </form>

        </div>

        @else

        <div class="alert alert-success mt-3">

            <i class="fas fa-check-circle"></i>

            <strong>
                Photo Session Completed.
            </strong>

            No further changes are allowed.

        </div>

        @endif

    </div>

</div>



{{-- ========================================================= --}}
{{-- IMAGE PREVIEW --}}
{{-- ========================================================= --}}

<div
    id="imagePreview"
    style="
        display:none;
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.85);
        z-index:9999;
        justify-content:center;
        align-items:center;
    "
>

    <img
        id="previewImage"
        src=""
        style="
            max-width:90%;
            max-height:90%;
            border-radius:10px;
            box-shadow:0 0 30px rgba(0,0,0,.6);
        "
    >

</div>



<script>

document.addEventListener('DOMContentLoaded', function () {


    /*
    |--------------------------------------------------------------------------
    | PHOTO UPLOAD
    |--------------------------------------------------------------------------
    */

    const photoInput =
        document.getElementById('photo_files');

    const photoDropZone =
        document.getElementById('photoDropZone');

    const selectedPhotoFiles =
        document.getElementById('selectedPhotoFiles');

    const photoUploadForm =
        document.getElementById('photoUploadForm');

    const uploadPhotosButton =
        document.getElementById('uploadPhotosButton');


    /*
    |--------------------------------------------------------------------------
    | SAFETY CHECK
    |--------------------------------------------------------------------------
    */

    if (
        !photoInput ||
        !photoDropZone ||
        !selectedPhotoFiles
    ) {

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | STORE SELECTED FILES
    |--------------------------------------------------------------------------
    |
    | Important:
    | We keep all files inside this array.
    |
    | This means:
    |
    | Drop file A
    | then paste file B
    |
    | File A will NOT disappear.
    |
    */

    let selectedFiles = [];


    /*
    |--------------------------------------------------------------------------
    | ALLOWED FILE TYPES
    |--------------------------------------------------------------------------
    */

    const allowedTypes = [
        'image/jpeg',
        'image/jpg',
        'image/png'
    ];


    const allowedExtensions = [
        'jpg',
        'jpeg',
        'png'
    ];


    /*
    |--------------------------------------------------------------------------
    | CHECK FILE
    |--------------------------------------------------------------------------
    */

    function isValidImage(file) {

        if (!file) {
            return false;
        }


        const extension =
            file.name
                .split('.')
                .pop()
                .toLowerCase();


        return (
            allowedTypes.includes(file.type) ||
            allowedExtensions.includes(extension)
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ADD FILES
    |--------------------------------------------------------------------------
    */

    function addFiles(files) {

        if (!files || files.length === 0) {
            return;
        }


        let invalidFiles = [];


        Array.from(files).forEach(function (file) {


            /*
            |--------------------------------------------------------------
            | Only images
            |--------------------------------------------------------------
            */

            if (!isValidImage(file)) {

                invalidFiles.push(file.name);

                return;

            }


            /*
            |--------------------------------------------------------------
            | Prevent duplicate file
            |--------------------------------------------------------------
            */

            const duplicate =
                selectedFiles.some(function (existingFile) {

                    return (
                        existingFile.name === file.name &&
                        existingFile.size === file.size &&
                        existingFile.lastModified === file.lastModified
                    );

                });


            if (!duplicate) {

                selectedFiles.push(file);

            }

        });


        /*
        |------------------------------------------------------------------
        | Show invalid warning
        |------------------------------------------------------------------
        */

        if (invalidFiles.length > 0) {

            alert(
                'Only JPG, JPEG, and PNG images are allowed.'
            );

        }


        /*
        |------------------------------------------------------------------
        | Update real input
        |------------------------------------------------------------------
        */

        syncInput();


        /*
        |------------------------------------------------------------------
        | Update display
        |------------------------------------------------------------------
        */

        renderSelectedFiles();

    }


    /*
    |--------------------------------------------------------------------------
    | SYNC FILE INPUT
    |--------------------------------------------------------------------------
    |
    | DataTransfer lets us rebuild the actual <input type="file">
    | without losing previously selected files.
    |
    */

    function syncInput() {

        const dataTransfer =
            new DataTransfer();


        selectedFiles.forEach(function (file) {

            dataTransfer.items.add(file);

        });


        photoInput.files =
            dataTransfer.files;

    }


    /*
    |--------------------------------------------------------------------------
    | DISPLAY SELECTED FILES
    |--------------------------------------------------------------------------
    */

    function renderSelectedFiles() {


        if (selectedFiles.length === 0) {

            selectedPhotoFiles.innerHTML = '';

            selectedPhotoFiles.style.display =
                'none';

            return;

        }


        selectedPhotoFiles.style.display =
            'block';


        let html = '';


        html += `
            <div class="card border">
                <div class="card-header bg-light">
                    <strong>
                        Selected Photos
                    </strong>

                    <span class="badge badge-primary ml-2">
                        ${selectedFiles.length}
                    </span>
                </div>

                <div class="card-body p-2">
        `;


        selectedFiles.forEach(function (file, index) {


            const fileSize =
                formatFileSize(file.size);


            html += `

                <div
                    class="d-flex align-items-center justify-content-between border rounded p-2 mb-2"
                    data-file-index="${index}"
                >

                    <div class="d-flex align-items-center">

                        <div class="mr-3">

                            <i
                                class="fas fa-image text-primary"
                                style="font-size:24px;"
                            ></i>

                        </div>

                        <div>

                            <div class="font-weight-bold">

                                ${escapeHtml(file.name)}

                            </div>

                            <small class="text-muted">

                                ${fileSize}

                            </small>

                        </div>

                    </div>


                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger removePhotoFile"
                        data-index="${index}"
                    >

                        <i class="fas fa-times"></i>

                    </button>

                </div>

            `;

        });


        html += `

                </div>

                <div class="card-footer bg-white">

                    <small class="text-muted">

                        ${selectedFiles.length}
                        photo(s) ready to upload.

                    </small>

                </div>

            </div>

        `;


        selectedPhotoFiles.innerHTML =
            html;

    }


    /*
    |--------------------------------------------------------------------------
    | FILE SIZE
    |--------------------------------------------------------------------------
    */

    function formatFileSize(bytes) {

        if (bytes === 0) {
            return '0 Bytes';
        }


        const units = [
            'Bytes',
            'KB',
            'MB',
            'GB'
        ];


        const index =
            Math.floor(
                Math.log(bytes) /
                Math.log(1024)
            );


        return (
            parseFloat(
                (
                    bytes /
                    Math.pow(1024, index)
                ).toFixed(2)
            ) +
            ' ' +
            units[index]
        );

    }


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    /*
    |--------------------------------------------------------------------------
    | BROWSE FILES
    |--------------------------------------------------------------------------
    */

    photoDropZone.addEventListener(
        'click',
        function () {

            photoInput.click();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | FILE INPUT CHANGE
    |--------------------------------------------------------------------------
    */

    photoInput.addEventListener(
        'change',
        function () {

            addFiles(this.files);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DRAG OVER
    |--------------------------------------------------------------------------
    */

    photoDropZone.addEventListener(
        'dragover',
        function (event) {

            event.preventDefault();

            event.stopPropagation();


            this.style.borderColor =
                '#007bff';

            this.style.backgroundColor =
                '#e9f3ff';

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DRAG LEAVE
    |--------------------------------------------------------------------------
    */

    photoDropZone.addEventListener(
        'dragleave',
        function (event) {

            event.preventDefault();

            event.stopPropagation();


            this.style.borderColor =
                '#adb5bd';

            this.style.backgroundColor =
                '#f8f9fa';

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DROP
    |--------------------------------------------------------------------------
    */

    photoDropZone.addEventListener(
        'drop',
        function (event) {

            event.preventDefault();

            event.stopPropagation();


            this.style.borderColor =
                '#adb5bd';

            this.style.backgroundColor =
                '#f8f9fa';


            const files =
                event.dataTransfer.files;


            addFiles(files);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | COPY / PASTE
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'paste',
        function (event) {


            /*
            |--------------------------------------------------------------
            | Only handle paste when Photo Session upload is available
            |--------------------------------------------------------------
            */

            if (!photoInput) {
                return;
            }


            const clipboardData =
                event.clipboardData ||
                window.clipboardData;


            if (!clipboardData) {
                return;
            }


            const files =
                clipboardData.files;


            /*
            |--------------------------------------------------------------
            | Clipboard files
            |--------------------------------------------------------------
            */

            if (
                files &&
                files.length > 0
            ) {

                const imageFiles = [];


                Array.from(files).forEach(function (file) {

                    if (isValidImage(file)) {

                        imageFiles.push(file);

                    }

                });


                if (imageFiles.length > 0) {

                    event.preventDefault();

                    addFiles(imageFiles);

                }

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | REMOVE SELECTED FILE
    |--------------------------------------------------------------------------
    */

    selectedPhotoFiles.addEventListener(
        'click',
        function (event) {


            const button =
                event.target.closest(
                    '.removePhotoFile'
                );


            if (!button) {
                return;
            }


            const index =
                parseInt(
                    button.dataset.index,
                    10
                );


            if (
                Number.isNaN(index) ||
                index < 0 ||
                index >= selectedFiles.length
            ) {

                return;

            }


            selectedFiles.splice(
                index,
                1
            );


            syncInput();

            renderSelectedFiles();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | FORM SUBMIT
    |--------------------------------------------------------------------------
    */

    if (photoUploadForm) {

        photoUploadForm.addEventListener(
            'submit',
            function (event) {


                /*
                |----------------------------------------------------------
                | Prevent empty submission
                |----------------------------------------------------------
                */

                if (selectedFiles.length === 0) {

                    event.preventDefault();


                    alert(
                        'Please select at least one photo.'
                    );


                    return;

                }


                /*
                |----------------------------------------------------------
                | Make absolutely sure input contains all files
                |----------------------------------------------------------
                */

                syncInput();


                /*
                |----------------------------------------------------------
                | Prevent double submission
                |----------------------------------------------------------
                */

                if (uploadPhotosButton) {

                    uploadPhotosButton.disabled =
                        true;


                    uploadPhotosButton.innerHTML = `

                        <i class="fas fa-spinner fa-spin"></i>

                        Uploading...

                    `;

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | IMAGE PREVIEW
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.preview-image')
        .forEach(function (img) {

            img.addEventListener(
                'click',
                function () {

                    document
                        .getElementById('previewImage')
                        .src =
                        this.dataset.image;


                    document
                        .getElementById('imagePreview')
                        .style.display =
                        'flex';

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | CLOSE IMAGE PREVIEW
    |--------------------------------------------------------------------------
    */

    const imagePreview =
        document.getElementById(
            'imagePreview'
        );


    if (imagePreview) {

        imagePreview.addEventListener(
            'click',
            function () {

                this.style.display =
                    'none';

            }
        );

    }

});

</script>

@endsection
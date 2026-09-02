@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h1 class="mb-1">

            <i class="fas fa-file-alt"></i>

            Create Job Order

        </h1>

        <small class="text-muted">

            Prepare the production job order for this order.

        </small>

    </div>


    <a
        href="{{ route('orders.show', $order) }}"
        class="btn btn-secondary"
    >

        <i class="fas fa-arrow-left"></i>

        Back to Order

    </a>

</div>


{{-- ========================================================= --}}
{{-- VALIDATION ERRORS --}}
{{-- ========================================================= --}}

@if($errors->any())

    <div class="alert alert-danger">

        <strong>
            Please check the following:
        </strong>

        <ul class="mb-0">

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif


{{-- ========================================================= --}}
{{-- ORDER INFORMATION --}}
{{-- ========================================================= --}}

<div class="card mb-3">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-shopping-cart"></i>

            Order Information

        </h5>

    </div>


    <div class="card-body">

        <div class="row">

            <div class="col-md-4">

                <strong>
                    Order No.
                </strong>

                <p class="mb-0">

                    {{ $order->order_no }}

                </p>

            </div>


            <div class="col-md-4">

                <strong>
                    Customer
                </strong>

                <p class="mb-0">

                    {{ $order->customer->customer_name }}

                </p>

            </div>


            <div class="col-md-4">

                <strong>
                    Due Date
                </strong>

                <p class="mb-0">

                    {{ $order->due_date }}

                </p>

            </div>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- JOB ORDER FORM --}}
{{-- ========================================================= --}}

<form
    action="{{ route('job-orders.store', $order) }}"
    method="POST"
    enctype="multipart/form-data"
    id="jobOrderForm"
>

    @csrf


    {{-- ========================================================= --}}
    {{-- JOB ORDER DETAILS --}}
    {{-- ========================================================= --}}

    <div class="card mb-3">

        <div
            class="card-header d-flex justify-content-between align-items-center"
        >

            <h5 class="mb-0">

                <i class="fas fa-list"></i>

                Job Order Details

            </h5>


            <button
                type="button"
                class="btn btn-primary btn-sm"
                id="addItemBtn"
            >

                <i class="fas fa-plus"></i>

                Add Item

            </button>

        </div>


        <div class="card-body">


            <div class="alert alert-info">

                <i class="fas fa-info-circle mr-1"></i>

                Add each product/design below. For every row,
                enter the name and jersey number if applicable,
                select the size, and enter the quantity.

                <strong>
                    Name and Number are optional.
                </strong>

            </div>


            {{-- ================================================= --}}
            {{-- ITEMS CONTAINER --}}
            {{-- ================================================= --}}

            <div id="itemsContainer"></div>


            {{-- ================================================= --}}
            {{-- GRAND TOTAL --}}
            {{-- ================================================= --}}

            <div class="card bg-light mt-3">

                <div class="card-body">

                    <div class="row">

                        <div class="col-md-8">

                            <strong>

                                Total Quantity

                            </strong>

                        </div>


                        <div class="col-md-4 text-right">

                            <strong>

                                <span id="grandTotal">
                                    0
                                </span>

                                PCS

                            </strong>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- JOB ORDER IMAGES --}}
    {{-- ========================================================= --}}

    <div class="card mb-3">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-images mr-1"></i>

                Job Order Images

                <small class="text-muted">
                    (Optional)
                </small>

            </h5>

        </div>


        <div class="card-body">


            <div class="mb-3">

                <label>

                    Images for Job Order

                </label>


                <p class="text-muted">

                    Upload images that should be included
                    in the production Job Order.

                </p>

            </div>


            {{-- ================================================= --}}
            {{-- REAL FILE INPUT --}}
            {{-- ================================================= --}}

            <input
                type="file"
                name="job_order_images[]"
                id="jobOrderImages"
                class="d-none"
                multiple
                accept="image/jpeg,image/png,image/jpg"
            >


            {{-- ================================================= --}}
            {{-- DROP ZONE --}}
            {{-- ================================================= --}}

            <div
                id="jobOrderImageDropZone"
                tabindex="0"
                class="border rounded text-center p-5"
                style="
                    border: 2px dashed #adb5bd !important;
                    background-color: #f8f9fa;
                    cursor: pointer;
                    transition: all 0.2s ease;
                "
            >

                <div class="mb-3">

                    <i
                        class="fas fa-cloud-upload-alt text-primary"
                        style="font-size: 48px;"
                    ></i>

                </div>


                <h5 class="mb-2">

                    Drag & Drop your images here

                </h5>


                <p class="text-muted mb-2">

                    or

                    <span class="text-primary font-weight-bold">

                        click here to browse

                    </span>

                </p>


                <div class="text-muted">

                    <i class="fas fa-paste mr-1"></i>

                    You can also paste images with

                    <strong>
                        Ctrl + V
                    </strong>

                </div>


                <small class="d-block text-muted mt-3">

                    Supported: JPG, JPEG, PNG

                    <br>

                    Maximum size: 5MB per image

                </small>

            </div>


            {{-- ================================================= --}}
            {{-- IMAGE COUNT --}}
            {{-- ================================================= --}}

            <div
                id="jobOrderImageCount"
                class="mt-3 text-muted"
                style="display: none;"
            >

                <i class="fas fa-images mr-1"></i>

                <span id="jobOrderImageCountText"></span>

            </div>


            {{-- ================================================= --}}
            {{-- IMAGE PREVIEW --}}
            {{-- ================================================= --}}

            <div
                id="jobOrderImagePreview"
                class="row mt-3"
            ></div>


        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- REMARKS --}}
    {{-- ========================================================= --}}

    <div class="card mb-3">

        <div class="card-header">

            <h5 class="mb-0">

                <i class="fas fa-comment-alt"></i>

                Remarks

            </h5>

        </div>


        <div class="card-body">

            <textarea
                name="remarks"
                id="remarks"
                class="form-control"
                rows="4"
                placeholder="Optional remarks for production"
            >{{ old('remarks') }}</textarea>

        </div>

    </div>


    {{-- ========================================================= --}}
    {{-- BUTTONS --}}
    {{-- ========================================================= --}}

    <div class="card">

        <div class="card-body">

            <div class="d-flex justify-content-end">

                <a
                    href="{{ route('orders.show', $order) }}"
                    class="btn btn-secondary mr-2"
                >

                    <i class="fas fa-times"></i>

                    Cancel

                </a>


                <button
                    type="submit"
                    class="btn btn-success"
                    id="saveJobOrderBtn"
                >

                    <i class="fas fa-save mr-1"></i>

                    Save Job Order

                </button>

            </div>

        </div>

    </div>

</form>


{{-- ========================================================= --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>

document.addEventListener(
    'DOMContentLoaded',
    function ()
    {


        /* =========================================================
           BASIC ELEMENTS
        ========================================================= */

        const itemsContainer =
            document.getElementById(
                'itemsContainer'
            );


        const addItemBtn =
            document.getElementById(
                'addItemBtn'
            );


        const grandTotal =
            document.getElementById(
                'grandTotal'
            );


        const jobOrderForm =
            document.getElementById(
                'jobOrderForm'
            );


        /* =========================================================
           SIZES
        ========================================================= */

        const sizes = [

            'XS',
            'S',
            'M',
            'L',
            'XL',
            '2XL',
            '3XL',
            '4XL',
            '5XL',
            '6XL',
            '7XL',
            '8XL',
            '9XL'

        ];


        /* =========================================================
           ITEM COUNTER
        ========================================================= */

        let itemCounter = 0;


        /* =========================================================
           JOB ORDER IMAGES
        ========================================================= */

        const jobOrderImagesInput =
            document.getElementById(
                'jobOrderImages'
            );


        const jobOrderImageDropZone =
            document.getElementById(
                'jobOrderImageDropZone'
            );


        const jobOrderImagePreview =
            document.getElementById(
                'jobOrderImagePreview'
            );


        const jobOrderImageCount =
            document.getElementById(
                'jobOrderImageCount'
            );


        const jobOrderImageCountText =
            document.getElementById(
                'jobOrderImageCountText'
            );


        let selectedJobOrderImages = [];


        const allowedJobOrderImageTypes = [

            'image/jpeg',
            'image/jpg',
            'image/png'

        ];


        const maxJobOrderImageSize =
            5 * 1024 * 1024;


        /* =========================================================
           ADD ITEM
        ========================================================= */

        function addItem()
        {

            itemCounter++;


            const itemId =
                itemCounter;


            const card =
                document.createElement(
                    'div'
                );


            card.className =
                'card border mb-4 job-item';


            card.dataset.itemId =
                itemId;


            card.innerHTML = `

                <div
                    class="card-header bg-light
                           d-flex
                           justify-content-between
                           align-items-center"
                >

                    <strong>

                        <i class="fas fa-tshirt mr-1"></i>

                        Item ${itemId}

                    </strong>


                    <button
                        type="button"
                        class="btn btn-danger btn-sm remove-item"
                    >

                        <i class="fas fa-trash mr-1"></i>

                        Remove

                    </button>

                </div>


                <div class="card-body">


                    {{-- ITEM / DESIGN NAME --}}

                    <div class="form-group">

                        <label>

                            Item / Design Name

                            <span class="text-danger">
                                *
                            </span>

                        </label>


                        <input
                            type="text"
                            name="items[${itemId}][item_name]"
                            class="form-control item-name"
                            placeholder="Example: Burgundy Jersey"
                            required
                        >

                    </div>


                    {{-- CUSTOMIZATION ROWS --}}

                    <div class="table-responsive">

                        <table
                            class="table table-bordered"
                        >

                            <thead class="table-light">

                                <tr>

                                    <th style="width: 28%;">

                                        Name on Shirt

                                        <small class="text-muted">
                                            (Optional)
                                        </small>

                                    </th>


                                    <th style="width: 18%;">

                                        Number

                                        <small class="text-muted">
                                            (Optional)
                                        </small>

                                    </th>


                                    <th style="width: 22%;">

                                        Size

                                        <span class="text-danger">
                                            *
                                        </span>

                                    </th>


                                    <th style="width: 22%;">

                                        Quantity

                                        <span class="text-danger">
                                            *
                                        </span>

                                    </th>


                                    <th style="width: 10%;">

                                        Action

                                    </th>

                                </tr>

                            </thead>


                            <tbody class="customization-rows">

                            </tbody>

                        </table>

                    </div>


                    {{-- ADD ROW --}}

                    <button
                        type="button"
                        class="btn btn-outline-primary btn-sm add-row"
                    >

                        <i class="fas fa-plus mr-1"></i>

                        Add Row

                    </button>


                    {{-- ITEM TOTAL --}}

                    <div class="text-right mt-3">

                        <strong>

                            Item Total:

                            <span class="item-total">
                                0
                            </span>

                            PCS

                        </strong>

                    </div>


                </div>

            `;


            itemsContainer.appendChild(
                card
            );


            addRow(card);


            updateGrandTotal();

        }


        /* =========================================================
           ADD ROW
        ========================================================= */

        function addRow(itemCard)
        {

            const tbody =
                itemCard.querySelector(
                    '.customization-rows'
                );


            const rowIndex =
                tbody.children.length;


            const itemId =
                itemCard.dataset.itemId;


            const row =
                document.createElement(
                    'tr'
                );


            row.className =
                'customization-row';


            row.innerHTML = `

                <td>

                    <input
                        type="text"
                        name="items[${itemId}][rows][${rowIndex}][name]"
                        class="form-control"
                        placeholder="Optional"
                    >

                </td>


                <td>

                    <input
                        type="text"
                        name="items[${itemId}][rows][${rowIndex}][number]"
                        class="form-control"
                        maxlength="20"
                        placeholder="Optional"
                    >

                </td>


                <td>

                    <select
                        name="items[${itemId}][rows][${rowIndex}][size]"
                        class="form-control"
                        required
                    >

                        <option value="">
                            Select Size
                        </option>

                        ${sizes.map(function(size) {

                            return `

                                <option value="${size}">
                                    ${size}
                                </option>

                            `;

                        }).join('')}

                    </select>

                </td>


                <td>

                    <input
                        type="number"
                        name="items[${itemId}][rows][${rowIndex}][quantity]"
                        class="form-control row-quantity"
                        min="1"
                        value="1"
                        required
                    >

                </td>


                <td class="text-center">

                    <button
                        type="button"
                        class="btn btn-danger btn-sm remove-row"
                        title="Remove row"
                    >

                        <i class="fas fa-trash"></i>

                    </button>

                </td>

            `;


            tbody.appendChild(
                row
            );


            updateItemTotal(
                itemCard
            );


            updateGrandTotal();

        }


        /* =========================================================
           REMOVE ITEM
        ========================================================= */

        itemsContainer.addEventListener(
            'click',
            function(event)
            {

                const button =
                    event.target.closest(
                        '.remove-item'
                    );


                if (!button) {

                    return;

                }


                const itemCard =
                    button.closest(
                        '.job-item'
                    );


                const itemCards =
                    itemsContainer.querySelectorAll(
                        '.job-item'
                    );


                if (
                    itemCards.length <= 1
                ) {

                    alert(
                        'At least one item is required.'
                    );

                    return;

                }


                if (
                    confirm(
                        'Remove this item?'
                    )
                ) {

                    itemCard.remove();

                    updateGrandTotal();

                }

            }
        );


        /* =========================================================
           ADD / REMOVE ROW
        ========================================================= */

        itemsContainer.addEventListener(
            'click',
            function(event)
            {

                const addRowButton =
                    event.target.closest(
                        '.add-row'
                    );


                if (addRowButton) {

                    const itemCard =
                        addRowButton.closest(
                            '.job-item'
                        );


                    addRow(
                        itemCard
                    );


                    return;

                }


                const removeRowButton =
                    event.target.closest(
                        '.remove-row'
                    );


                if (!removeRowButton) {

                    return;

                }


                const itemCard =
                    removeRowButton.closest(
                        '.job-item'
                    );


                const tbody =
                    itemCard.querySelector(
                        '.customization-rows'
                    );


                if (
                    tbody.children.length <= 1
                ) {

                    alert(
                        'Each item must have at least one row.'
                    );

                    return;

                }


                removeRowButton
                    .closest('tr')
                    .remove();


                updateItemTotal(
                    itemCard
                );


                updateGrandTotal();

            }
        );


        /* =========================================================
           QUANTITY CHANGE
        ========================================================= */

        itemsContainer.addEventListener(
            'input',
            function(event)
            {

                if (
                    !event.target.classList.contains(
                        'row-quantity'
                    )
                ) {

                    return;

                }


                const itemCard =
                    event.target.closest(
                        '.job-item'
                    );


                updateItemTotal(
                    itemCard
                );


                updateGrandTotal();

            }
        );


        /* =========================================================
           ITEM TOTAL
        ========================================================= */

        function updateItemTotal(
            itemCard
        )
        {

            let total = 0;


            itemCard
                .querySelectorAll(
                    '.row-quantity'
                )
                .forEach(
                    function(input)
                    {

                        total +=
                            parseInt(
                                input.value
                            ) || 0;

                    }
                );


            const itemTotal =
                itemCard.querySelector(
                    '.item-total'
                );


            if (itemTotal) {

                itemTotal.textContent =
                    total;

            }

        }


        /* =========================================================
           GRAND TOTAL
        ========================================================= */

        function updateGrandTotal()
        {

            let total = 0;


            itemsContainer
                .querySelectorAll(
                    '.row-quantity'
                )
                .forEach(
                    function(input)
                    {

                        total +=
                            parseInt(
                                input.value
                            ) || 0;

                    }
                );


            grandTotal.textContent =
                total;

        }


        /* =========================================================
           IMAGE VALIDATION
        ========================================================= */

        function isValidJobOrderImage(
            file
        )
        {

            if (
                !allowedJobOrderImageTypes.includes(
                    file.type
                )
            ) {

                alert(
                    file.name +
                    ' is not a valid image. Please use JPG, JPEG, or PNG.'
                );

                return false;

            }


            if (
                file.size >
                maxJobOrderImageSize
            ) {

                alert(
                    file.name +
                    ' is larger than 5MB.'
                );

                return false;

            }


            return true;

        }


        /* =========================================================
           ADD JOB ORDER IMAGES
        ========================================================= */

        function addJobOrderImages(
            files
        )
        {

            Array
                .from(files)
                .forEach(
                    function(file)
                    {

                        if (
                            !isValidJobOrderImage(
                                file
                            )
                        ) {

                            return;

                        }


                        const duplicate =
                            selectedJobOrderImages.some(
                                function(existingFile)
                                {

                                    return (

                                        existingFile.name ===
                                            file.name

                                        &&

                                        existingFile.size ===
                                            file.size

                                        &&

                                        existingFile.lastModified ===
                                            file.lastModified

                                    );

                                }
                            );


                        if (duplicate) {

                            return;

                        }


                        selectedJobOrderImages.push(
                            file
                        );

                    }
                );


            syncJobOrderImageInput();

            renderJobOrderImagePreview();

        }


        /* =========================================================
           SYNC IMAGE INPUT
        ========================================================= */

        function syncJobOrderImageInput()
        {

            if (!jobOrderImagesInput) {

                return;

            }


            const dataTransfer =
                new DataTransfer();


            selectedJobOrderImages.forEach(
                function(file)
                {

                    dataTransfer.items.add(
                        file
                    );

                }
            );


            jobOrderImagesInput.files =
                dataTransfer.files;

        }


        /* =========================================================
           RENDER IMAGE PREVIEW
        ========================================================= */

        function renderJobOrderImagePreview()
        {

            if (!jobOrderImagePreview) {

                return;

            }


            jobOrderImagePreview.innerHTML =
                '';


            if (
                selectedJobOrderImages.length === 0
            ) {

                jobOrderImageCount.style.display =
                    'none';

                return;

            }


            jobOrderImageCount.style.display =
                'block';


            jobOrderImageCountText.textContent =

                selectedJobOrderImages.length +

                (
                    selectedJobOrderImages.length === 1
                        ? ' image selected'
                        : ' images selected'
                );


            selectedJobOrderImages.forEach(
                function(file, index)
                {

                    const col =
                        document.createElement(
                            'div'
                        );


                    col.className =
                        'col-md-4 col-lg-3 mb-3';


                    const card =
                        document.createElement(
                            'div'
                        );


                    card.className =
                        'card h-100 shadow-sm';


                    const imageWrapper =
                        document.createElement(
                            'div'
                        );


                    imageWrapper.style.position =
                        'relative';


                    imageWrapper.style.height =
                        '180px';


                    imageWrapper.style.backgroundColor =
                        '#f8f9fa';


                    const image =
                        document.createElement(
                            'img'
                        );


                    image.className =
                        'card-img-top';


                    image.style.height =
                        '180px';


                    image.style.width =
                        '100%';


                    image.style.objectFit =
                        'contain';


                    image.style.padding =
                        '8px';


                    const reader =
                        new FileReader();


                    reader.onload =
                        function(event)
                        {

                            image.src =
                                event.target.result;

                        };


                    reader.readAsDataURL(
                        file
                    );


                    /* REMOVE BUTTON */

                    const removeButton =
                        document.createElement(
                            'button'
                        );


                    removeButton.type =
                        'button';


                    removeButton.className =
                        'btn btn-danger btn-sm';


                    removeButton.innerHTML =
                        '<i class="fas fa-times"></i>';


                    removeButton.title =
                        'Remove image';


                    removeButton.style.position =
                        'absolute';


                    removeButton.style.top =
                        '8px';


                    removeButton.style.right =
                        '8px';


                    removeButton.addEventListener(
                        'click',
                        function()
                        {

                            selectedJobOrderImages.splice(
                                index,
                                1
                            );


                            syncJobOrderImageInput();

                            renderJobOrderImagePreview();

                        }
                    );


                    imageWrapper.appendChild(
                        image
                    );


                    imageWrapper.appendChild(
                        removeButton
                    );


                    /* FILE INFO */

                    const cardBody =
                        document.createElement(
                            'div'
                        );


                    cardBody.className =
                        'card-body p-2';


                    const fileName =
                        document.createElement(
                            'small'
                        );


                    fileName.className =
                        'd-block text-truncate';


                    fileName.title =
                        file.name;


                    fileName.textContent =
                        file.name;


                    const fileSize =
                        document.createElement(
                            'small'
                        );


                    fileSize.className =
                        'text-muted';


                    fileSize.textContent =

                        (
                            file.size /
                            1024 /
                            1024
                        ).toFixed(2)

                        +

                        ' MB';


                    cardBody.appendChild(
                        fileName
                    );


                    cardBody.appendChild(
                        fileSize
                    );


                    card.appendChild(
                        imageWrapper
                    );


                    card.appendChild(
                        cardBody
                    );


                    col.appendChild(
                        card
                    );


                    jobOrderImagePreview.appendChild(
                        col
                    );

                }
            );

        }


        /* =========================================================
           IMAGE DROP ZONE - CLICK
        ========================================================= */

        jobOrderImageDropZone.addEventListener(
            'click',
            function()
            {

                jobOrderImagesInput.click();

            }
        );


        /* =========================================================
           IMAGE INPUT CHANGE
        ========================================================= */

        jobOrderImagesInput.addEventListener(
            'change',
            function()
            {

                const files =
                    Array.from(
                        this.files
                    );


                addJobOrderImages(
                    files
                );


                /*
                IMPORTANT:
                Clear the real input after capturing
                the files so the same file can be
                selected again later.
                */

                this.value =
                    '';

            }
        );


        /* =========================================================
           DRAG OVER
        ========================================================= */

        jobOrderImageDropZone.addEventListener(
            'dragover',
            function(event)
            {

                event.preventDefault();

                event.stopPropagation();


                this.style.borderColor =
                    '#007bff';


                this.style.backgroundColor =
                    '#eaf3ff';

            }
        );


        /* =========================================================
           DRAG ENTER
        ========================================================= */

        jobOrderImageDropZone.addEventListener(
            'dragenter',
            function(event)
            {

                event.preventDefault();

                event.stopPropagation();

            }
        );


        /* =========================================================
           DRAG LEAVE
        ========================================================= */

        jobOrderImageDropZone.addEventListener(
            'dragleave',
            function(event)
            {

                event.preventDefault();

                event.stopPropagation();


                this.style.borderColor =
                    '#adb5bd';


                this.style.backgroundColor =
                    '#f8f9fa';

            }
        );


        /* =========================================================
           DROP
        ========================================================= */

        jobOrderImageDropZone.addEventListener(
            'drop',
            function(event)
            {

                event.preventDefault();

                event.stopPropagation();


                this.style.borderColor =
                    '#adb5bd';


                this.style.backgroundColor =
                    '#f8f9fa';


                const files =
                    event.dataTransfer.files;


                if (
                    files &&
                    files.length > 0
                ) {

                    addJobOrderImages(
                        files
                    );

                }

            }
        );


        /* =========================================================
           PASTE IMAGE
        ========================================================= */

        document.addEventListener(
            'paste',
            function(event)
            {

                const items =
                    event.clipboardData
                        ? event.clipboardData.items
                        : [];


                if (!items || !items.length) {

                    return;

                }


                const imageFiles = [];


                Array
                    .from(items)
                    .forEach(
                        function(item)
                        {

                            if (
                                item.kind === 'file'
                                &&
                                item.type.startsWith(
                                    'image/'
                                )
                            ) {

                                const file =
                                    item.getAsFile();


                                if (file) {

                                    imageFiles.push(
                                        file
                                    );

                                }

                            }

                        }
                    );


                if (
                    imageFiles.length > 0
                ) {

                    addJobOrderImages(
                        imageFiles
                    );

                }

            }
        );


        /* =========================================================
           ADD ITEM BUTTON
        ========================================================= */

        addItemBtn.addEventListener(
            'click',
            function()
            {

                addItem();

            }
        );


        /* =========================================================
           FORM SUBMIT PROTECTION
        ========================================================= */

        jobOrderForm.addEventListener(
            'submit',
            function(event)
            {

                const itemCards =
                    itemsContainer.querySelectorAll(
                        '.job-item'
                    );


                if (
                    itemCards.length === 0
                ) {

                    event.preventDefault();


                    alert(
                        'Please add at least one item before saving the Job Order.'
                    );


                    return;

                }


                /*
                Make absolutely sure the latest
                selected images are attached to
                the real file input before submit.
                */

                syncJobOrderImageInput();


                const saveButton =
                    document.getElementById(
                        'saveJobOrderBtn'
                    );


                if (saveButton) {

                    saveButton.disabled =
                        true;


                    saveButton.innerHTML = `

                        <i class="fas fa-spinner fa-spin mr-1"></i>

                        Saving...

                    `;

                }

            }
        );


        /* =========================================================
           START WITH ONE ITEM
        ========================================================= */

        addItem();


        /* =========================================================
           INITIAL TOTAL
        ========================================================= */

        updateGrandTotal();

    }

);

</script>

@endsection
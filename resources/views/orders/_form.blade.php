{{-- ========================================================= --}}
{{-- ORDER INFORMATION --}}
{{-- ========================================================= --}}

<div class="card shadow-sm mb-4">

    <div class="card-header bg-primary text-white">

        <h5 class="mb-0">

            <i class="fas fa-file-invoice"></i>

            Order Information

        </h5>

    </div>

    <div class="card-body">

        <div class="row">

            {{-- ================================================= --}}
            {{-- CUSTOMER --}}
            {{-- ================================================= --}}

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Customer

                    <span class="text-danger">*</span>

                </label>


                <input
                    type="hidden"
                    name="customer_id"
                    id="customerSelect"
                    value="{{ old(
                        'customer_id',
                        $order->customer_id ?? ''
                    ) }}">


                <div class="input-group">

                    <input
                        type="text"
                        id="customerSearch"
                        class="form-control"
                        placeholder="Search customer name, company or phone..."
                        autocomplete="off"
                        value="{{ old(
                            'customer_id',
                            $order->customer_id ?? ''
                        )
                            ? optional(
                                $customers->firstWhere(
                                    'id',
                                    old(
                                        'customer_id',
                                        $order->customer_id ?? ''
                                    )
                                )
                            )->customer_name
                            : ''
                        }}">


                    <button
                        type="button"
                        class="btn btn-success"
                        data-toggle="modal"
                        data-target="#quickCustomerModal">

                        <i class="fas fa-plus"></i>

                        New

                    </button>

                </div>


                {{-- Search Results --}}

                <div
                    id="customerResults"
                    class="list-group position-absolute w-100"
                    style="
                        z-index: 1050;
                        display: none;
                        max-height: 250px;
                        overflow-y: auto;
                    ">
                </div>


                {{-- Selected Customer --}}

                <div
                    id="selectedCustomer"
                    class="mt-2"
                    style="display: none;">

                    <div class="alert alert-success mb-0">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>

                                <i class="fas fa-user-check mr-1"></i>

                                <strong id="selectedCustomerName"></strong>

                                <div
                                    id="selectedCustomerDetails"
                                    class="small text-muted mt-1">
                                </div>

                            </div>


                            <button
                                type="button"
                                class="btn btn-sm btn-outline-danger"
                                id="clearCustomer">

                                <i class="fas fa-times"></i>

                                Change

                            </button>

                        </div>

                    </div>

                </div>


                <small class="text-muted">

                    Search by customer name, company or phone number.

                </small>

            </div>


            @php

                $customerData = $customers->map(function ($customer) {

                    return [

                        'id' => $customer->id,

                        'name' =>
                            $customer->customer_name,

                        'company' =>
                            $customer->company,

                        'phone' =>
                            $customer->phone,

                        'email' =>
                            $customer->email,

                    ];

                })->values();

            @endphp


            <script>

                const customers = @json($customerData);

            </script>


            {{-- ================================================= --}}
            {{-- CUSTOMER SEARCH SCRIPT --}}
            {{-- ================================================= --}}

            <script>

                const customerSearch =
                    document.getElementById('customerSearch');

                const customerResults =
                    document.getElementById('customerResults');

                const customerSelect =
                    document.getElementById('customerSelect');

                const selectedCustomer =
                    document.getElementById('selectedCustomer');

                const selectedCustomerName =
                    document.getElementById('selectedCustomerName');

                const selectedCustomerDetails =
                    document.getElementById('selectedCustomerDetails');

                const clearCustomer =
                    document.getElementById('clearCustomer');


                function selectCustomer(customer)
                {

                    customerSelect.value =
                        customer.id;


                    customerSearch.value =
                        customer.name;


                    selectedCustomerName.textContent =
                        customer.name;


                    let details = [];


                    if (customer.company) {

                        details.push(
                            customer.company
                        );

                    }


                    if (customer.phone) {

                        details.push(
                            customer.phone
                        );

                    }


                    if (customer.email) {

                        details.push(
                            customer.email
                        );

                    }


                    selectedCustomerDetails.textContent =
                        details.join(' • ');


                    selectedCustomer.style.display =
                        'block';


                    customerResults.style.display =
                        'none';


                    customerSearch.style.display =
                        'none';

                }


                customerSearch.addEventListener(
                    'input',
                    function ()
                    {

                        const keyword =
                            this.value
                                .toLowerCase()
                                .trim();


                        customerResults.innerHTML =
                            '';


                        if (!keyword) {

                            customerResults.style.display =
                                'none';

                            return;

                        }


                        const matches =
                            customers
                                .filter(function(customer)
                                {

                                    const name =
                                        customer.name
                                            ? customer.name.toLowerCase()
                                            : '';


                                    const company =
                                        customer.company
                                            ? customer.company.toLowerCase()
                                            : '';


                                    const phone =
                                        customer.phone
                                            ? customer.phone.toLowerCase()
                                            : '';


                                    return (

                                        name.includes(keyword)

                                        ||

                                        company.includes(keyword)

                                        ||

                                        phone.includes(keyword)

                                    );

                                })
                                .slice(0, 10);


                        if (matches.length === 0) {

                            customerResults.innerHTML = `

                                <div class="list-group-item text-muted">

                                    <i class="fas fa-search mr-1"></i>

                                    No customer found.

                                </div>

                            `;

                            customerResults.style.display =
                                'block';

                            return;

                        }


                        matches.forEach(function(customer)
                        {

                            const item =
                                document.createElement('button');


                            item.type =
                                'button';


                            item.className =
                                'list-group-item list-group-item-action';


                            item.innerHTML = `

                                <div class="d-flex justify-content-between">

                                    <strong>
                                        ${customer.name}
                                    </strong>

                                </div>

                                <small class="text-muted">

                                    ${
                                        customer.company
                                            ? customer.company
                                            : ''
                                    }

                                    ${
                                        customer.phone
                                            ? ' • ' + customer.phone
                                            : ''
                                    }

                                </small>

                            `;


                            item.addEventListener(
                                'click',
                                function ()
                                {

                                    selectCustomer(
                                        customer
                                    );

                                }
                            );


                            customerResults.appendChild(
                                item
                            );

                        });


                        customerResults.style.display =
                            'block';

                    }
                );


                clearCustomer.addEventListener(
                    'click',
                    function ()
                    {

                        customerSelect.value =
                            '';

                        customerSearch.value =
                            '';

                        customerSearch.style.display =
                            'block';

                        selectedCustomer.style.display =
                            'none';

                        customerSearch.focus();

                    }
                );


                document.addEventListener(
                    'click',
                    function (event)
                    {

                        if (
                            !customerSearch.contains(
                                event.target
                            )

                            &&

                            !customerResults.contains(
                                event.target
                            )
                        ) {

                            customerResults.style.display =
                                'none';

                        }

                    }
                );

            </script>


            {{-- ================================================= --}}
            {{-- DESIGNER --}}
            {{-- ================================================= --}}

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Designer

                </label>

                <select
                    name="designer_id"
                    class="form-control">

                    <option value="">

                        -- Not Assigned --

                    </option>

                    @foreach($designers as $designer)

                        <option
                            value="{{ $designer->id }}"
                            {{ old(
                                'designer_id',
                                $order->designer_id ?? ''
                            ) == $designer->id
                                ? 'selected'
                                : ''
                            }}>

                            {{ $designer->name }}

                        </option>

                    @endforeach

                </select>

            </div>


            {{-- ================================================= --}}
            {{-- DUE DATE --}}
            {{-- ================================================= --}}

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Due Date

                    <span class="text-danger">*</span>

                </label>

                <input
                    type="date"
                    name="due_date"
                    class="form-control"
                    value="{{ old(
                        'due_date',
                        $order->due_date ?? ''
                    ) }}"
                    required>

            </div>


            {{-- ================================================= --}}
            {{-- DELIVERY METHOD --}}
            {{-- ================================================= --}}

            <div class="col-md-6 mb-3">

                <label class="form-label">

                    Delivery Method

                </label>

                <select
                    name="delivery_method"
                    class="form-control"
                    required>

                    <option
                        value="Self Pickup"
                        {{ old(
                            'delivery_method',
                            $order->delivery_method ?? ''
                        ) == 'Self Pickup'
                            ? 'selected'
                            : ''
                        }}>

                        Self Pickup

                    </option>

                    <option
                        value="Delivery"
                        {{ old(
                            'delivery_method',
                            $order->delivery_method ?? ''
                        ) == 'Delivery'
                            ? 'selected'
                            : ''
                        }}>

                        Delivery

                    </option>

                </select>

            </div>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- CUSTOMER BRIEF --}}
{{-- ========================================================= --}}

<div class="card shadow-sm mb-4">

    <div class="card-header bg-info text-white">

        <h5 class="mb-0">

            <i class="fas fa-comments"></i>

            Customer Brief

        </h5>

    </div>

    <div class="card-body">

        <div class="alert alert-info">

            Paste the customer's requirements from WhatsApp, Telegram or other communication.

        </div>

        <textarea
            name="customer_brief"
            rows="7"
            class="form-control"
            placeholder="Example:

• Round Neck Jersey

• Black & Gold Theme

• Front follow sample A

• Sleeve add sponsor logo

• Back with player name & number">{{ old(
    'customer_brief',
    $order->customer_brief ?? ''
) }}</textarea>

    </div>

</div>


{{-- ========================================================= --}}
{{-- INTERNAL NOTES --}}
{{-- ========================================================= --}}

<div class="card shadow-sm mb-4">

    <div class="card-header bg-secondary text-white">

        <h5 class="mb-0">

            <i class="fas fa-user-shield"></i>

            Internal Notes

        </h5>

    </div>

    <div class="card-body">

        <textarea
            name="remarks"
            rows="5"
            class="form-control"
            placeholder="Example:

VIP Customer

Rush Order

Payment Pending

Need owner approval">{{ old(
    'remarks',
    $order->remarks ?? ''
) }}</textarea>

    </div>

</div>


{{-- ========================================================= --}}
{{-- CUSTOMER REFERENCE CENTER --}}
{{-- ========================================================= --}}

<div class="card shadow-sm mb-4">

    <div class="card-header bg-warning">

        <h5 class="mb-0">

            <i class="fas fa-paperclip"></i>

            Customer Reference Center

        </h5>

    </div>

    <div class="card-body">

        <div class="alert alert-warning">

            <i class="fas fa-lightbulb"></i>

            Upload all files and links received from the customer before creating the order.

        </div>


        {{-- ========================================================= --}}
{{-- REFERENCE FILES - DRAG DROP + CLICK + PASTE --}}
{{-- ========================================================= --}}

<div class="mb-4">

    <label class="form-label fw-bold">

        <i class="fas fa-folder-open text-primary"></i>

        Reference Files

    </label>


    {{-- Hidden real file input --}}

    <input
        type="file"
        name="reference_files[]"
        id="reference_files"
        class="d-none"
        multiple
        accept=".jpg,.jpeg,.png,.gif,.webp,.pdf,.doc,.docx,.txt,.rtf,.xls,.xlsx,.csv,.ppt,.pptx,.ai,.eps,.svg,.psd,.cdr,.otf,.ttf"
    >


    {{-- Upload Drop Zone --}}

    <div
        id="referenceDropZone"
        class="border rounded text-center p-4"
        tabindex="0"
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
                style="font-size: 42px;"
            ></i>

        </div>


        <h5 class="mb-2">

            Drag & Drop your files here

        </h5>


        <p class="text-muted mb-2">

            or

            <span class="text-primary font-weight-bold">

                click here to browse

            </span>

        </p>


        <div class="text-muted">

            <i class="fas fa-paste mr-1"></i>

            You can also paste files or images with

            <strong>Ctrl + V</strong>

        </div>


        <small class="d-block text-muted mt-3">
            Supported:
            JPG, PNG, PDF, AI, EPS, SVG, PSD, CDR, OTF, TTF
        </small>

    </div>

</div>


{{-- Selected Files --}}

<div id="selectedFiles">

    <div class="alert alert-light text-muted">

        <i class="fas fa-folder-open"></i>

        No files selected.

    </div>

</div>


        {{-- Reference Links --}}

        <label class="form-label fw-bold">

            <i class="fas fa-link text-success"></i>

            Reference Links

        </label>


        <div id="linkContainer">

            <div class="input-group mb-2">

                <input
                    type="url"
                    name="reference_links[]"
                    class="form-control"
                    placeholder="https://">

                <button
                    type="button"
                    class="btn btn-success"
                    id="addLink">

                    <i class="fas fa-plus"></i>

                </button>

            </div>

        </div>

    </div>

</div>



{{-- ========================================================= --}}
{{-- PRODUCT JAVASCRIPT --}}
{{-- ========================================================= --}}

<script>


// ============================================================
// REFERENCE FILES
// CLICK + DRAG & DROP + CTRL + V
// ============================================================

const referenceFilesInput =
    document.getElementById('reference_files');

const referenceDropZone =
    document.getElementById('referenceDropZone');

const selectedFilesContainer =
    document.getElementById('selectedFiles');


// Keep all selected files here
let selectedReferenceFiles = [];


// ============================================================
// ESCAPE HTML
// ============================================================

function escapeHtml(value)
{
    const div =
        document.createElement('div');

    div.textContent =
        value;

    return div.innerHTML;
}


// ============================================================
// FORMAT FILE SIZE
// ============================================================

function formatFileSize(bytes)
{
    if (!bytes) {

        return '0 KB';

    }


    const kb =
        bytes / 1024;


    if (kb < 1024) {

        return kb.toFixed(1) + ' KB';

    }


    const mb =
        kb / 1024;


    return mb.toFixed(1) + ' MB';
}


// ============================================================
// CHECK DUPLICATE FILE
// ============================================================

function isDuplicateFile(file)
{
    return selectedReferenceFiles.some(
        function(existingFile)
        {

            return (

                existingFile.name === file.name

                &&

                existingFile.size === file.size

                &&

                existingFile.lastModified ===
                    file.lastModified

            );

        }
    );
}


// ============================================================
// ADD FILES
// ============================================================

function addReferenceFiles(files)
{
    if (!files || files.length === 0) {

        return;

    }


    Array.from(files).forEach(
        function(file)
        {

            if (!isDuplicateFile(file)) {

                selectedReferenceFiles.push(file);

            }

        }
    );


    syncReferenceFilesInput();

    renderSelectedFiles();
}


// ============================================================
// SYNC REAL INPUT
// ============================================================

function syncReferenceFilesInput()
{
    if (!referenceFilesInput) {

        return;

    }


    const dataTransfer =
        new DataTransfer();


    selectedReferenceFiles.forEach(
        function(file)
        {

            dataTransfer.items.add(file);

        }
    );


    referenceFilesInput.files =
        dataTransfer.files;
}


// ============================================================
// RENDER SELECTED FILES
// ============================================================

function renderSelectedFiles()
{
    if (!selectedFilesContainer) {

        return;

    }


    selectedFilesContainer.innerHTML = '';


    if (
        selectedReferenceFiles.length === 0
    ) {

        selectedFilesContainer.innerHTML = `

            <div class="alert alert-light text-muted">

                <i class="fas fa-folder-open mr-1"></i>

                No files selected.

            </div>

        `;

        return;

    }


    const list =
        document.createElement('div');


    list.className =
        'list-group';


    selectedReferenceFiles.forEach(
        function(file, index)
        {

            const item =
                document.createElement('div');


            item.className =
                'list-group-item';


            item.innerHTML = `

                <div class="d-flex align-items-center justify-content-between">

                    <div
                        class="d-flex align-items-center text-truncate mr-3"
                        style="min-width: 0;"
                    >

                        <div class="mr-3">

                            <i
                                class="fas fa-file text-primary"
                                style="font-size: 20px;"
                            ></i>

                        </div>


                        <div class="text-truncate">

                            <div class="font-weight-bold text-truncate">

                                ${escapeHtml(file.name)}

                            </div>


                            <small class="text-muted">

                                ${formatFileSize(file.size)}

                            </small>

                        </div>

                    </div>


                    <button
                        type="button"
                        class="btn btn-sm btn-outline-danger removeReferenceFile"
                        data-index="${index}"
                        title="Remove file"
                    >

                        <i class="fas fa-times"></i>

                    </button>

                </div>

            `;


            list.appendChild(item);

        }
    );


    selectedFilesContainer.appendChild(list);
}


// ============================================================
// CLICK DROP ZONE → OPEN FILE BROWSER
// ============================================================

if (referenceDropZone) {

    referenceDropZone.addEventListener(
        'click',
        function()
        {

            if (referenceFilesInput) {

                referenceFilesInput.click();

            }

        }
    );

}


// ============================================================
// FILE INPUT CHANGE
// ============================================================

if (referenceFilesInput) {

    referenceFilesInput.addEventListener(
        'change',
        function()
        {

            addReferenceFiles(
                this.files
            );


            // Reset input value.

            // This allows the user to select
            // the same file again after removing it.

            this.value = '';

        }
    );

}


// ============================================================
// DRAG OVER
// ============================================================

if (referenceDropZone) {

    referenceDropZone.addEventListener(
        'dragover',
        function(e)
        {

            e.preventDefault();

            e.stopPropagation();


            this.style.borderColor =
                '#007bff';

            this.style.backgroundColor =
                '#eaf3ff';

        }
    );

}


// ============================================================
// DRAG ENTER
// ============================================================

if (referenceDropZone) {

    referenceDropZone.addEventListener(
        'dragenter',
        function(e)
        {

            e.preventDefault();

            e.stopPropagation();


            this.style.borderColor =
                '#007bff';

            this.style.backgroundColor =
                '#eaf3ff';

        }
    );

}


// ============================================================
// DRAG LEAVE
// ============================================================

if (referenceDropZone) {

    referenceDropZone.addEventListener(
        'dragleave',
        function(e)
        {

            e.preventDefault();

            e.stopPropagation();


            this.style.borderColor =
                '#adb5bd';

            this.style.backgroundColor =
                '#f8f9fa';

        }
    );

}


// ============================================================
// DROP FILES
// ============================================================

if (referenceDropZone) {

    referenceDropZone.addEventListener(
        'drop',
        function(e)
        {

            e.preventDefault();

            e.stopPropagation();


            this.style.borderColor =
                '#adb5bd';

            this.style.backgroundColor =
                '#f8f9fa';


            const files =
                e.dataTransfer.files;


            addReferenceFiles(files);

        }
    );

}


// ============================================================
// CTRL + V / PASTE
// ============================================================

document.addEventListener(
    'paste',
    function(e)
    {

        // Only handle paste when the user
        // is interacting with the reference
        // upload area.

        const activeElement =
            document.activeElement;


        const isUploadAreaFocused =
            activeElement === referenceDropZone
            ||
            referenceDropZone?.contains(
                activeElement
            );


        if (!isUploadAreaFocused) {

            return;

        }


        const clipboardData =
            e.clipboardData;


        if (!clipboardData) {

            return;

        }


        const pastedFiles = [];


        // Check clipboard items

        Array.from(
            clipboardData.items
        ).forEach(
            function(item)
            {

                if (
                    item.kind === 'file'
                ) {

                    const file =
                        item.getAsFile();


                    if (file) {

                        pastedFiles.push(
                            file
                        );

                    }

                }

            }
        );


        // If clipboard contains files/images

        if (pastedFiles.length > 0) {

            e.preventDefault();


            addReferenceFiles(
                pastedFiles
            );

        }

    }
);


// ============================================================
// REMOVE SELECTED FILE
// ============================================================

document.addEventListener(
    'click',
    function(e)
    {

        const button =
            e.target.closest(
                '.removeReferenceFile'
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
            Number.isNaN(index)
            ||
            index < 0
            ||
            index >= selectedReferenceFiles.length
        ) {

            return;

        }


        selectedReferenceFiles.splice(
            index,
            1
        );


        syncReferenceFilesInput();

        renderSelectedFiles();

    }
);


// ============================================================
// INITIAL RENDER
// ============================================================

renderSelectedFiles();


// ============================================================
// ADD REFERENCE LINK
// ============================================================
// ============================================================

document
    .getElementById('addLink')
    .addEventListener(
        'click',
        function()
        {

            const row =
                document.createElement(
                    'div'
                );


            row.className =
                'input-group mb-2';


            row.innerHTML = `

                <input
                    type="url"
                    name="reference_links[]"
                    class="form-control"
                    placeholder="https://">

                <button
                    type="button"
                    class="btn btn-danger removeLink">

                    <i class="fas fa-minus"></i>

                </button>

            `;


            document
                .getElementById(
                    'linkContainer'
                )
                .appendChild(row);

        }
    );


// ============================================================
// REMOVE REFERENCE LINK
// ============================================================

document.addEventListener(
    'click',
    function(e)
    {

        const btn =
            e.target.closest(
                '.removeLink'
            );


        if (!btn) {

            return;

        }


        btn
            .closest('.input-group')
            .remove();

    }
);


</script>


{{-- ========================================================= --}}
{{-- QUICK CREATE CUSTOMER MODAL --}}
{{-- ========================================================= --}}

<div
    class="modal fade"
    id="quickCustomerModal"
    tabindex="-1"
    role="dialog"
    aria-labelledby="quickCustomerModalLabel"
    aria-hidden="true">

    <div
        class="modal-dialog modal-lg"
        role="document">

        <div class="modal-content">


            {{-- HEADER --}}

            <div class="modal-header bg-success text-white">

                <h5
                    class="modal-title"
                    id="quickCustomerModalLabel">

                    <i class="fas fa-user-plus mr-1"></i>

                    Create New Customer

                </h5>


                <button
                    type="button"
                    class="close text-white"
                    data-dismiss="modal"
                    aria-label="Close">

                    <span aria-hidden="true">
                        &times;
                    </span>

                </button>

            </div>


            {{-- BODY --}}

            <div class="modal-body">

                <div class="alert alert-info">

                    <i class="fas fa-info-circle mr-1"></i>

                    Create the customer here and it will automatically
                    be selected for this order.

                </div>


                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Customer Name

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="quickCustomerName"
                            class="form-control"
                            placeholder="Customer name">

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Company

                        </label>

                        <input
                            type="text"
                            id="quickCustomerCompany"
                            class="form-control"
                            placeholder="Company name">

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Phone

                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            id="quickCustomerPhone"
                            class="form-control"
                            placeholder="0123456789">

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Email

                        </label>

                        <input
                            type="email"
                            id="quickCustomerEmail"
                            class="form-control"
                            placeholder="customer@email.com">

                    </div>


                    <div class="col-md-12 mb-3">

                        <label class="form-label">

                            Address

                        </label>

                        <textarea
                            id="quickCustomerAddress"
                            class="form-control"
                            rows="3"
                            placeholder="Customer address"></textarea>

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            State

                        </label>

                        <input
                            type="text"
                            id="quickCustomerState"
                            class="form-control"
                            placeholder="State">

                    </div>


                    <div class="col-md-6 mb-3">

                        <label class="form-label">

                            Postcode

                        </label>

                        <input
                            type="text"
                            id="quickCustomerPostcode"
                            class="form-control"
                            placeholder="Postcode">

                    </div>


                    <div class="col-md-12 mb-3">

                        <label class="form-label">

                            Remarks

                        </label>

                        <textarea
                            id="quickCustomerRemarks"
                            class="form-control"
                            rows="2"
                            placeholder="Optional remarks"></textarea>

                    </div>

                </div>

            </div>


            {{-- FOOTER --}}

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn btn-secondary"
                    data-dismiss="modal">

                    <i class="fas fa-times mr-1"></i>

                    Cancel

                </button>


                <button
                    type="button"
                    class="btn btn-success"
                    id="saveQuickCustomer">

                    <i class="fas fa-save mr-1"></i>

                    Save & Select Customer

                </button>

            </div>

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- QUICK CUSTOMER SCRIPT --}}
{{-- ========================================================= --}}

<script>

document
    .getElementById('saveQuickCustomer')
    .addEventListener(
        'click',
        function()
        {

            const button = this;


            const customerName =
                document
                    .getElementById(
                        'quickCustomerName'
                    )
                    .value
                    .trim();


            const phone =
                document
                    .getElementById(
                        'quickCustomerPhone'
                    )
                    .value
                    .trim();


            const company =
                document
                    .getElementById(
                        'quickCustomerCompany'
                    )
                    .value
                    .trim();


            const email =
                document
                    .getElementById(
                        'quickCustomerEmail'
                    )
                    .value
                    .trim();


            const address =
                document
                    .getElementById(
                        'quickCustomerAddress'
                    )
                    .value
                    .trim();


            const state =
                document
                    .getElementById(
                        'quickCustomerState'
                    )
                    .value
                    .trim();


            const postcode =
                document
                    .getElementById(
                        'quickCustomerPostcode'
                    )
                    .value
                    .trim();


            const remarks =
                document
                    .getElementById(
                        'quickCustomerRemarks'
                    )
                    .value
                    .trim();


            if (!customerName) {

                alert(
                    'Customer name is required.'
                );

                document
                    .getElementById(
                        'quickCustomerName'
                    )
                    .focus();

                return;

            }


            if (!phone) {

                alert(
                    'Phone number is required.'
                );

                document
                    .getElementById(
                        'quickCustomerPhone'
                    )
                    .focus();

                return;

            }


            button.disabled = true;


            button.innerHTML = `

                <i class="fas fa-spinner fa-spin mr-1"></i>

                Creating...

            `;


            fetch(
                "{{ route('orders.quickCustomer') }}",
                {

                    method: 'POST',

                    headers: {

                        'Content-Type':
                            'application/json',

                        'Accept':
                            'application/json',

                        'X-CSRF-TOKEN':
                            document
                                .querySelector(
                                    'meta[name="csrf-token"]'
                                )
                                .getAttribute(
                                    'content'
                                ),

                    },

                    body: JSON.stringify({

                        customer_name:
                            customerName,

                        phone:
                            phone,

                        company:
                            company,

                        email:
                            email,

                        address:
                            address,

                        state:
                            state,

                        postcode:
                            postcode,

                        remarks:
                            remarks,

                    }),

                }
            )

            .then(
                async response => {

                    const data =
                        await response.json();


                    if (!response.ok) {

                        throw data;

                    }


                    return data;

                }
            )

            .then(
                data => {

                    if (!data.success) {

                        throw data;

                    }


                    const customer =
                        data.customer;


                    document
                        .getElementById(
                            'customerSelect'
                        )
                        .value =
                        customer.id;


                    document
                        .getElementById(
                            'customerSearch'
                        )
                        .value =
                        customer.customer_name;


                    document
                        .getElementById(
                            'selectedCustomerName'
                        )
                        .textContent =
                        customer.customer_name;


                    let details = [];


                    if (customer.company) {

                        details.push(
                            customer.company
                        );

                    }


                    if (customer.phone) {

                        details.push(
                            customer.phone
                        );

                    }


                    document
                        .getElementById(
                            'selectedCustomerDetails'
                        )
                        .textContent =
                        details.join(' • ');


                    document
                        .getElementById(
                            'selectedCustomer'
                        )
                        .style.display =
                        'block';


                    document
                        .getElementById(
                            'customerSearch'
                        )
                        .style.display =
                        'none';


                    document
                        .getElementById(
                            'customerResults'
                        )
                        .style.display =
                        'none';


                    $('#quickCustomerModal')
                        .modal('hide');


                    document.getElementById(
                        'quickCustomerName'
                    ).value = '';


                    document.getElementById(
                        'quickCustomerCompany'
                    ).value = '';


                    document.getElementById(
                        'quickCustomerPhone'
                    ).value = '';


                    document.getElementById(
                        'quickCustomerEmail'
                    ).value = '';


                    document.getElementById(
                        'quickCustomerAddress'
                    ).value = '';


                    document.getElementById(
                        'quickCustomerState'
                    ).value = '';


                    document.getElementById(
                        'quickCustomerPostcode'
                    ).value = '';


                    document.getElementById(
                        'quickCustomerRemarks'
                    ).value = '';


                    alert(
                        'Customer created and selected successfully.'
                    );

                }
            )

            .catch(
                error => {

                    console.error(error);


                    let message =
                        'Unable to create customer.';


                    if (error.errors) {

                        const errors =
                            Object
                                .values(
                                    error.errors
                                )
                                .flat();


                        if (
                            errors.length > 0
                        ) {

                            message =
                                errors.join('\n');

                        }

                    }


                    alert(message);

                }
            )

            .finally(
                () => {

                    button.disabled =
                        false;


                    button.innerHTML = `

                        <i class="fas fa-save mr-1"></i>

                        Save & Select Customer

                    `;

                }
            );

        }
    );

</script>
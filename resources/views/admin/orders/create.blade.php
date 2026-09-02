@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <h1 class="mb-1">

                <i class="fas fa-plus-circle mr-2"></i>

                Create New Order

            </h1>

            <p class="text-muted mb-0">

                Create and manage a new customer order.

            </p>

        </div>


        <div>

            <a
                href="{{ route('admin.orders.index') }}"
                class="btn btn-secondary"
            >

                <i class="fas fa-arrow-left mr-1"></i>

                Back to Orders

            </a>

        </div>

    </div>

</div>


<div class="card mt-3">

    <div class="card-header">

        <h5 class="mb-0">

            <i class="fas fa-file-alt mr-2"></i>

            New Order Information

        </h5>

    </div>


    <div class="card-body">

        <form
            action="{{ route('admin.orders.store') }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf


            {{-- Existing Order Form --}}
            @include('orders._form')


        </form>

    </div>

</div>


<script>

function calculateTotals() {

    let grandTotal = 0;


    document
        .querySelectorAll('#itemsTable tbody tr')
        .forEach(function(row) {

            let qty =
                parseFloat(
                    row.querySelector('.qty')?.value
                ) || 0;

            let price =
                parseFloat(
                    row.querySelector('.price')?.value
                ) || 0;


            let subtotal =
                qty * price;


            let subtotalInput =
                row.querySelector('.subtotal');


            if (subtotalInput) {

                subtotalInput.value =
                    subtotal.toFixed(2);

            }


            grandTotal += subtotal;

        });


    let grandTotalElement =
        document.getElementById('grandTotal');


    if (grandTotalElement) {

        grandTotalElement.innerHTML =
            'RM ' + grandTotal.toFixed(2);

    }

}


// =================================================
// ADD ITEM ROW
// =================================================

document.addEventListener(
    'click',
    function(e) {

        const btn =
            e.target.closest('#addRow');


        if (!btn) return;


        let tbody =
            document.querySelector(
                '#itemsTable tbody'
            );


        if (!tbody) return;


        let firstRow =
            tbody.querySelector('tr');


        if (!firstRow) return;


        let newRow =
            firstRow.cloneNode(true);


        newRow
            .querySelectorAll('input')
            .forEach(function(input) {

                if (
                    input.classList.contains('qty')
                ) {

                    input.value = 1;

                }

                else if (
                    input.classList.contains('price')
                ) {

                    input.value = 0;

                }

                else if (
                    input.classList.contains('subtotal')
                ) {

                    input.value = '0.00';

                }

                else {

                    input.value = '';

                }

            });


        tbody.appendChild(newRow);


        calculateTotals();

    }
);


// =================================================
// REMOVE ITEM ROW
// =================================================

document.addEventListener(
    'click',
    function(e) {

        const btn =
            e.target.closest('.removeRow');


        if (!btn) return;


        let rows =
            document.querySelectorAll(
                '#itemsTable tbody tr'
            );


        if (rows.length <= 1) {

            alert(
                'At least one product is required.'
            );

            return;

        }


        btn.closest('tr').remove();


        calculateTotals();

    }
);


// =================================================
// LIVE TOTAL CALCULATION
// =================================================

document.addEventListener(
    'input',
    function(e) {

        if (
            e.target.classList.contains('qty') ||
            e.target.classList.contains('price')
        ) {

            calculateTotals();

        }

    }
);


// =================================================
// INITIAL CALCULATION
// =================================================

calculateTotals();

</script>

@endsection
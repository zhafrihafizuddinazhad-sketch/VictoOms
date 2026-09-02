@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">

        <h3>Create Order</h3>

    </div>

    <div class="card-body">

        <form
    action="{{ route('orders.store') }}"
    method="POST"
    enctype="multipart/form-data">

    @csrf
    @include('orders._form')

        </form>

    </div>

</div>

    

<script>

function calculateTotals() {

    let grandTotal = 0;

    document.querySelectorAll('#itemsTable tbody tr').forEach(function(row){

        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;

        let subtotal = qty * price;

        row.querySelector('.subtotal').value = subtotal.toFixed(2);

        grandTotal += subtotal;

    });

    document.getElementById('grandTotal').innerHTML =
        'RM ' + grandTotal.toFixed(2);

}

// Add Row
document.getElementById('addRow').addEventListener('click', function(){

    let tbody = document.querySelector('#itemsTable tbody');

    let firstRow = tbody.querySelector('tr');

    let newRow = firstRow.cloneNode(true);

    newRow.querySelectorAll('input').forEach(function(input){

        if(input.classList.contains('qty')){

            input.value = 1;

        }else if(input.classList.contains('price')){

            input.value = 0;

        }else if(input.classList.contains('subtotal')){

            input.value = '0.00';

        }else{

            input.value = '';

        }

    });

    tbody.appendChild(newRow);

    calculateTotals();

});

// Remove Row
document.addEventListener('click', function(e){

    const btn = e.target.closest('.removeRow');

    if(!btn) return;

    let rows = document.querySelectorAll('#itemsTable tbody tr');

    if(rows.length <= 1){

        alert('At least one product is required.');

        return;

    }

    btn.closest('tr').remove();

    calculateTotals();

});

// Live Calculate
document.addEventListener('input', function(e){

    if(
        e.target.classList.contains('qty') ||
        e.target.classList.contains('price')
    ){

        calculateTotals();

    }

});

calculateTotals();

</script>

@endsection
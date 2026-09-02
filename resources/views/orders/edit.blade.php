@extends('layouts.admin')

@section('content')

<div class="card">

    <div class="card-header">

        <h3>Edit Order</h3>

    </div>

    <div class="card-body">

        <form action="{{ route('orders.update',$order) }}" method="POST">

    @csrf
    @method('PUT')

    <div class="row">

        <div class="col-md-6">

            <label class="form-label">Customer</label>

            <select name="customer_id" class="form-control" required>

                <option value="">Select Customer</option>

                @foreach($customers as $customer)

                <option
                    value="{{ $customer->id }}"
                    {{ $order->customer_id == $customer->id ? 'selected' : '' }}>

                {{ $customer->customer_name }}

</option>

@endforeach

            </select>

        </div>

        <div class="col-md-6">

            <label class="form-label">Due Date</label>

            <input
                type="date"
    name="due_date"
    class="form-control"
    value="{{ $order->due_date }}"
    required>

        </div>

    </div>

   <div class="row mt-3">

    <div class="col-md-6">

        <label class="form-label">Assign Designer</label>

        <select name="designer_id" class="form-control">

            <option value="">-- Select Designer --</option>

            @foreach($designers as $designer)

                <option value="{{ $designer->id }}"
                    {{ $order->designer_id == $designer->id ? 'selected' : '' }}>

                    {{ $designer->name }}

                </option>

            @endforeach

        </select>

    </div>

    <div class="col-md-6">

        <label class="form-label">Delivery Method</label>

        <select name="delivery_method" class="form-control">

            <option value="Self Pickup"
                {{ $order->delivery_method == 'Self Pickup' ? 'selected' : '' }}>
                Self Pickup
            </option>

            <option value="Delivery"
                {{ $order->delivery_method == 'Delivery' ? 'selected' : '' }}>
                Delivery
            </option>

        </select>

    </div>

</div>

    <div class="mt-3">

        <label class="form-label">Remarks</label>

        <textarea
            name="remarks"
            rows="3"
            class="form-control">{{ $order->remarks }} </textarea>

    </div>

    <hr>

    <h5 class="mt-4">Products</h5>

<table class="table table-bordered" id="itemsTable">

    <thead class="table-light">

        <tr>

            <th width="40%">Product</th>

            <th width="15%">Qty</th>

            <th width="20%">Unit Price</th>

            <th width="20%">Subtotal</th>

            <th width="5%"></th>

        </tr>

    </thead>

    <tbody>

@foreach($order->items as $item)

<tr>

    <td>

        <input
            type="text"
            name="product_name[]"
            class="form-control"
            value="{{ $item->product_name }}"
            required>

    </td>

    <td>

        <input
            type="number"
            name="quantity[]"
            class="form-control qty"
            value="{{ $item->quantity }}"
            min="1"
            required>

    </td>

    <td>

        <input
            type="number"
            step="0.01"
            name="unit_price[]"
            class="form-control price"
            value="{{ $item->unit_price }}"
            required>

    </td>

    <td>

        <input
            type="text"
            class="form-control subtotal"
            value="{{ number_format($item->subtotal,2,'.','') }}"
            readonly>

    </td>

    <td>

        <button
            type="button"
            class="btn btn-danger btn-sm removeRow">

            <i class="fas fa-trash"></i>

        </button>

    </td>

</tr>

@endforeach

</tbody>

</table>

<button
    type="button"
    id="addRow"
    class="btn btn-primary">

    <i class="fas fa-plus"></i>

    Add Product

</button>

<hr>

<h4 class="text-end">

Grand Total :

<span id="grandTotal">

RM0.00

</span>

</h4>

<button type="submit" class="btn btn-warning">
    <i class="fas fa-save"></i> Update Order
</button>

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
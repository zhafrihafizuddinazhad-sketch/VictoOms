@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <h1>Customer Details</h1>

    <div>
        <a href="{{ route('customers.index') }}" class="btn btn-secondary">
            Back
        </a>

        <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning">
            Edit
        </a>
    </div>

</div>

<div class="card">

    <div class="card-body">

        <table class="table table-bordered">

            <tr>
                <th width="200">Customer Name</th>
                <td>{{ $customer->customer_name }}</td>
            </tr>

            <tr>
                <th>Phone</th>
                <td>{{ $customer->phone }}</td>
            </tr>

            <tr>
                <th>Email</th>
                <td>{{ $customer->email ?: '-' }}</td>
            </tr>

            <tr>
                <th>Company</th>
                <td>{{ $customer->company ?: '-' }}</td>
            </tr>

            <tr>
                <th>Address</th>
                <td>{{ $customer->address ?: '-' }}</td>
            </tr>

            <tr>
                <th>State</th>
                <td>{{ $customer->state ?: '-' }}</td>
            </tr>

            <tr>
                <th>Postcode</th>
                <td>{{ $customer->postcode ?: '-' }}</td>
            </tr>

            <tr>
                <th>Remarks</th>
                <td>{{ $customer->remarks ?: '-' }}</td>
            </tr>

        </table>

    </div>

</div>

@endsection
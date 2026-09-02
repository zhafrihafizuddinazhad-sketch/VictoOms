@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Edit Customer</h1>

    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="card">
    <div class="card-body">

        <form action="{{ route('customers.update', $customer) }}" method="POST">

            @csrf
            @method('PUT')

            @include('customers._form')

            <button type="submit" class="btn btn-warning">
                Update Customer
            </button>

        </form>

    </div>
</div>

@endsection
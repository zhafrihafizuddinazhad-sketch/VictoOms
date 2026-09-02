@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1>Add Customer</h1>

    <a href="{{ route('customers.index') }}" class="btn btn-secondary">
        Back
    </a>
</div>

<div class="card">
    <div class="card-body">

        <form action="{{ route('customers.store') }}" method="POST">

            @csrf

            @include('customers._form')

            <button type="submit" class="btn btn-primary">
                Save Customer
            </button>

        </form>

    </div>
</div>

@endsection
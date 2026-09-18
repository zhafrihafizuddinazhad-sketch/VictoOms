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


    <div class="d-flex justify-content-end mt-4">
    <a href="{{ route('orders.index') }}" class="btn btn-secondary mr-2">
        Cancel
    </a>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save"></i>
        Create Order
    </button>
</div>

        </form>

    </div>

</div>



@endsection
@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <h1>Customers</h1>

    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Customer
    </a>

</div>

@if(session('success'))
<div class="alert alert-success">
    {{ session('success') }}
</div>
@endif

<div class="card">
    <div class="card-body">

        <form action="{{ route('customers.index') }}" method="GET" class="mb-3">

            <div class="row">

                <div class="col-md-4">

                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Search customer..."
                           value="{{ $search }}">

                </div>

                <div class="col-md-3">

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>

                    <a href="{{ route('customers.index') }}"
                       class="btn btn-secondary">
                        Reset
                    </a>

                </div>

            </div>

        </form>

        <table class="table table-bordered table-hover">

            <thead class="table-dark">

                <tr>

                    <th>Customer</th>
                    <th>Contact</th>
                    <th>Company</th>
                    <th width="180">Action</th>

                </tr>

            </thead>

            <tbody>

            @forelse($customers as $customer)

                <tr>

                    <td>

                        <strong>{{ $customer->customer_name }}</strong>

                        <br>

                        <small class="text-muted">
                            Customer ID #{{ $customer->id }}
                        </small>

                    </td>

                    <td>

                        <i class="fas fa-phone text-success"></i>
                        {{ $customer->phone }}

                        <br>

                        <small class="text-muted">

                            <i class="fas fa-envelope"></i>

                            {{ $customer->email ?: '-' }}

                        </small>

                    </td>

                    <td>

                        <i class="fas fa-building text-primary"></i>

                        {{ $customer->company ?: '-' }}

                    </td>

                    <td class="text-center">

                        <a href="{{ route('customers.show', $customer) }}"
                           class="btn btn-info btn-sm"
                           title="View">

                            <i class="fas fa-eye"></i>

                        </a>

                        <a href="{{ route('customers.edit', $customer) }}"
                           class="btn btn-warning btn-sm"
                           title="Edit">

                            <i class="fas fa-edit"></i>

                        </a>

                        <form action="{{ route('customers.destroy', $customer) }}"
                              method="POST"
                              style="display:inline;">

                            @csrf
                            @method('DELETE')

                            <button
                                type="submit"
                                class="btn btn-danger btn-sm"
                                title="Delete"
                                onclick="return confirm('Are you sure you want to delete this customer?')">

                                <i class="fas fa-trash"></i>

                            </button>

                        </form>

                    </td>

                </tr>

            @empty

                <tr>

                    <td colspan="4" class="text-center py-4">

                        <i class="fas fa-users fa-2x text-secondary mb-2"></i>

                        <br>

                        No customers found.

                    </td>

                </tr>

            @endforelse

            </tbody>

        </table>

        <div class="mt-3">
            {{ $customers->appends(request()->query())->links() }}
        </div>

    </div>

</div>

@endsection
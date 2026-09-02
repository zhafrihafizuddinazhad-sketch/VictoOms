@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h1 class="mb-1">
            <i class="fas fa-box"></i>
            Products
        </h1>

        <small class="text-muted">
            Manage products available for orders.
        </small>

    </div>


    <a
        href="{{ route('products.create') }}"
        class="btn btn-primary">

        <i class="fas fa-plus"></i>

        Add Product

    </a>

</div>


@if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

        {{ session('success') }}

        <button
            type="button"
            class="close"
            data-dismiss="alert">

            <span>&times;</span>

        </button>

    </div>

@endif


<div class="card">

    <div class="card-header">

        <form
            method="GET"
            action="{{ route('products.index') }}">

            <div class="input-group">

                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search product or category..."
                    value="{{ $search }}">

                <div class="input-group-append">

                    <button class="btn btn-primary">

                        <i class="fas fa-search"></i>

                        Search

                    </button>

                </div>

            </div>

        </form>

    </div>


    <div class="card-body p-0">

        <div class="table-responsive">

            <table class="table table-hover mb-0">

                <thead>

                    <tr>

                        <th>#</th>

                        <th>Product</th>

                        <th>Category</th>

                        <th>Default Price</th>

                        <th>Status</th>

                        <th width="180">Action</th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($products as $product)

                        <tr>

                            <td>
                                {{ $products->firstItem() + $loop->index }}
                            </td>


                            <td>

                                <strong>
                                    {{ $product->product_name }}
                                </strong>

                                @if($product->description)

                                    <br>

                                    <small class="text-muted">

                                        {{ Str::limit($product->description, 60) }}

                                    </small>

                                @endif

                            </td>


                            <td>

                                {{ $product->category ?: '-' }}

                            </td>


                            <td>

                                RM {{ number_format($product->default_price, 2) }}

                            </td>


                            <td>

                                @if($product->is_active)

                                    <span class="badge badge-success">

                                        Active

                                    </span>

                                @else

                                    <span class="badge badge-secondary">

                                        Inactive

                                    </span>

                                @endif

                            </td>


                            <td>

                                <a
                                    href="{{ route('products.edit', $product) }}"
                                    class="btn btn-sm btn-warning">

                                    <i class="fas fa-edit"></i>

                                </a>


                                <form
                                    action="{{ route('products.toggle', $product) }}"
                                    method="POST"
                                    class="d-inline">

                                    @csrf

                                    @method('PATCH')

                                    <button
                                        type="submit"
                                        class="btn btn-sm {{ $product->is_active ? 'btn-danger' : 'btn-success' }}"
                                        title="{{ $product->is_active ? 'Deactivate' : 'Activate' }}">

                                        <i class="fas {{ $product->is_active ? 'fa-ban' : 'fa-check' }}"></i>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td
                                colspan="6"
                                class="text-center text-muted py-4">

                                No products found.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>


    @if($products->hasPages())

        <div class="card-footer">

            {{ $products->links() }}

        </div>

    @endif

</div>

@endsection
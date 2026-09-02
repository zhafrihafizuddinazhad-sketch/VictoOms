@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <div>

        <h1>
            <i class="fas fa-edit"></i>
            Edit Product
        </h1>

    </div>


    <a
        href="{{ route('products.index') }}"
        class="btn btn-secondary">

        <i class="fas fa-arrow-left"></i>

        Back

    </a>

</div>


@if($errors->any())

    <div class="alert alert-danger">

        <strong>Please check the following:</strong>

        <ul class="mb-0">

            @foreach($errors->all() as $error)

                <li>{{ $error }}</li>

            @endforeach

        </ul>

    </div>

@endif


<div class="card">

    <div class="card-header">

        <h5 class="mb-0">

            {{ $product->product_name }}

        </h5>

    </div>


    <form
        action="{{ route('products.update', $product) }}"
        method="POST">

        @csrf

        @method('PUT')


        <div class="card-body">

            <div class="row">


                <div class="col-md-6 mb-3">

                    <label>

                        Product Name
                        <span class="text-danger">*</span>

                    </label>

                    <input
                        type="text"
                        name="product_name"
                        class="form-control"
                        value="{{ old('product_name', $product->product_name) }}"
                        required>

                </div>


                <div class="col-md-6 mb-3">

                    <label>
                        Category
                    </label>

                    <input
                        type="text"
                        name="category"
                        class="form-control"
                        value="{{ old('category', $product->category) }}">

                </div>


                <div class="col-md-6 mb-3">

                    <label>

                        Default Price
                        <span class="text-danger">*</span>

                    </label>

                    <div class="input-group">

                        <div class="input-group-prepend">

                            <span class="input-group-text">
                                RM
                            </span>

                        </div>

                        <input
                            type="number"
                            name="default_price"
                            class="form-control"
                            step="0.01"
                            min="0"
                            value="{{ old('default_price', $product->default_price) }}"
                            required>

                    </div>

                </div>


                <div class="col-md-12 mb-3">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        class="form-control"
                        rows="4">{{ old('description', $product->description) }}</textarea>

                </div>


            </div>

        </div>


        <div class="card-footer text-right">

            <a
                href="{{ route('products.index') }}"
                class="btn btn-secondary">

                Cancel

            </a>


            <button
                type="submit"
                class="btn btn-primary">

                <i class="fas fa-save"></i>

                Update Product

            </button>

        </div>

    </form>

</div>

@endsection
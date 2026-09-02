@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="d-flex justify-content-between align-items-center">

        <div>

            <h1 class="mb-1">

                <i class="fas fa-edit mr-2"></i>

                Edit Order

            </h1>

            <p class="text-muted mb-0">

                Update order information and details.

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

            Order Information

            <span class="text-muted ml-2">

                {{ $order->order_no }}

            </span>

        </h5>

    </div>


    <div class="card-body">

        <form
            action="{{ route('admin.orders.update', $order) }}"
            method="POST"
            enctype="multipart/form-data"
        >

            @csrf

            @method('PUT')


            @include('orders._form')


            <div class="mt-4 pt-3 border-top">

                <div class="d-flex justify-content-between">

                    <a
                        href="{{ route('admin.orders.index') }}"
                        class="btn btn-secondary"
                    >

                        <i class="fas fa-times mr-1"></i>

                        Cancel

                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >

                        <i class="fas fa-save mr-1"></i>

                        Update Order

                    </button>

                </div>

            </div>


        </form>

    </div>

</div>

@endsection
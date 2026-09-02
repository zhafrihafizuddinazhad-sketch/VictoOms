{{-- ================================================= --}}
{{-- QUICK ACTIONS --}}
{{-- ================================================= --}}

<div class="card mt-3">

    <div class="card-header">

        <h4 class="mb-0">

            <i class="fas fa-bolt text-warning mr-2"></i>

            Quick Actions

        </h4>

    </div>


    <div class="card-body">

        <div class="row">


            {{-- ================================================= --}}
            {{-- CREATE ORDER --}}
            {{-- ================================================= --}}

            <div class="col-md-3 mb-3">

                <a
                    href="{{ route('admin.orders.create') }}"
                    class="btn btn-primary btn-block py-3"
                >

                    <i class="fas fa-plus fa-lg mb-2"></i>

                    <br>

                    Create Order

                </a>

            </div>


            {{-- ================================================= --}}
            {{-- MANAGE ORDERS --}}
            {{-- ================================================= --}}

            <div class="col-md-3 mb-3">

                <a
                    href="{{ route('admin.orders.index') }}"
                    class="btn btn-info btn-block py-3"
                >

                    <i class="fas fa-shopping-cart fa-lg mb-2"></i>

                    <br>

                    Manage Orders

                </a>

            </div>


            {{-- ================================================= --}}
            {{-- CUSTOMERS --}}
            {{-- ================================================= --}}

            <div class="col-md-3 mb-3">

                <a
                    href="{{ route('admin.customers.index') }}"
                    class="btn btn-success btn-block py-3"
                >

                    <i class="fas fa-users fa-lg mb-2"></i>

                    <br>

                    Customers

                </a>

            </div>


        </div>

    </div>

</div>
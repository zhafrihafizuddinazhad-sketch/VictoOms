{{-- ================================================= --}}
{{-- RECENT ORDERS --}}
{{-- ================================================= --}}

<div class="card mt-3">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">

            <i class="fas fa-history text-primary mr-2"></i>

            Recent Orders

        </h4>


        <a
            href="{{ route('orders.index') }}"
            class="btn btn-outline-primary btn-sm">

            View All Orders

            <i class="fas fa-arrow-right ml-1"></i>

        </a>

    </div>


    <div class="card-body p-0">

        @forelse($recentOrders as $order)

            <div class="border-bottom p-3">

                <div class="row align-items-center">


                    {{-- Order --}}
                    <div class="col-md-3">

                        <strong>

                            {{ $order->order_no }}

                        </strong>

                        <br>

                        <small class="text-muted">

                            {{ $order->customer->customer_name ?? 'Unknown Customer' }}

                        </small>

                    </div>


                    {{-- Status --}}
                    <div class="col-md-3">

                        <span
                            class="badge {{ $order->getStatusBadgeClass() }}">

                            {{ $order->status }}

                        </span>

                    </div>


                    {{-- Due Date --}}
                    <div class="col-md-3">

                        <small class="text-muted">

                            Due Date

                        </small>

                        <br>

                        @if($order->due_date)

                            {{ \Carbon\Carbon::parse(
                                $order->due_date
                            )->format('d M Y') }}

                        @else

                            <span class="text-muted">

                                No due date

                            </span>

                        @endif

                    </div>


                    {{-- Action --}}
                    <div class="col-md-3 text-md-right">

                        <a
                            href="{{ route(
                                'orders.show',
                                $order
                            ) }}"
                            class="btn btn-primary btn-sm">

                            <i class="fas fa-eye mr-1"></i>

                            View

                        </a>

                    </div>

                </div>

            </div>

        @empty

            <div class="text-center py-5 text-muted">

                <i class="fas fa-shopping-cart fa-2x mb-2"></i>

                <p class="mb-0">

                    No orders found.

                </p>

            </div>

        @endforelse

    </div>

</div>
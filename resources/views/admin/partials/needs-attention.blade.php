{{-- ================================================= --}}
{{-- NEEDS ATTENTION --}}
{{-- ================================================= --}}

<div class="card mt-3">

    <div class="card-header">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h4 class="mb-0">

                    <i class="fas fa-exclamation-triangle text-danger mr-2"></i>

                    Needs Attention

                </h4>

                <small class="text-muted">

                    Orders that may require immediate action

                </small>

            </div>


            @if($attentionOrders->count())

                <span class="badge badge-danger">

                    {{ $attentionOrders->count() }}

                    {{ $attentionOrders->count() == 1 ? 'Order' : 'Orders' }}

                </span>

            @endif

        </div>

    </div>


    <div class="card-body p-0">

        @forelse($attentionOrders as $order)

            <div class="border-bottom p-3">

                <div class="row align-items-center">


                    {{-- ========================================= --}}
                    {{-- ORDER INFORMATION --}}
                    {{-- ========================================= --}}

                    <div class="col-md-3">

                        <strong>

                            {{ $order->order_no }}

                        </strong>


                        <br>


                        <small class="text-muted">

                            {{ $order->customer->customer_name ?? 'Unknown Customer' }}

                        </small>

                    </div>


                    {{-- ========================================= --}}
                    {{-- STATUS --}}
                    {{-- ========================================= --}}

                    <div class="col-md-3">

                        @if($order->status === 'Pending Approval')

                            <span class="badge badge-warning">

                                <i class="fas fa-hourglass-half mr-1"></i>

                                Pending Approval

                            </span>


                        @elseif($order->status === 'Pending')

                            <span class="badge badge-danger">

                                <i class="fas fa-user-slash mr-1"></i>

                                Pending

                            </span>


                        @elseif(
                            $order->due_date &&
                            \Carbon\Carbon::parse(
                                $order->due_date
                            )->isPast() &&
                            !in_array(
                                $order->status,
                                [
                                    'Completed',
                                    'Cancelled'
                                ]
                            )
                        )

                            <span class="badge badge-danger">

                                <i class="fas fa-clock mr-1"></i>

                                Overdue

                            </span>


                        @else

                            <span class="badge badge-secondary">

                                {{ $order->status }}

                            </span>

                        @endif

                    </div>


                    {{-- ========================================= --}}
                    {{-- DUE DATE --}}
                    {{-- ========================================= --}}

                    <div class="col-md-3">

                        <small class="text-muted">

                            Due Date

                        </small>


                        <br>


                        @if($order->due_date)

                            @if(
                                \Carbon\Carbon::parse(
                                    $order->due_date
                                )->isPast() &&
                                !in_array(
                                    $order->status,
                                    [
                                        'Completed',
                                        'Cancelled'
                                    ]
                                )
                            )

                                <strong class="text-danger">

                                    {{ \Carbon\Carbon::parse(
                                        $order->due_date
                                    )->format('d M Y') }}

                                </strong>

                            @else

                                <strong>

                                    {{ \Carbon\Carbon::parse(
                                        $order->due_date
                                    )->format('d M Y') }}

                                </strong>

                            @endif

                        @else

                            <span class="text-muted">

                                No due date

                            </span>

                        @endif

                    </div>


                    {{-- ========================================= --}}
                    {{-- ACTION --}}
                    {{-- ========================================= --}}

                    <div class="col-md-3 text-md-right">

                        <a
                            href="{{ route(
                                'admin.orders.show',
                                $order
                            ) }}"
                            class="btn btn-primary btn-sm"
                        >

                            <i class="fas fa-eye mr-1"></i>

                            View Order

                        </a>

                    </div>

                </div>

            </div>


        @empty


            {{-- ========================================= --}}
            {{-- NO ATTENTION ORDERS --}}
            {{-- ========================================= --}}

            <div class="text-center py-5 text-muted">

                <i
                    class="fas fa-check-circle fa-2x mb-2 text-success"
                ></i>


                <p class="mb-0">

                    No orders require attention.

                </p>

            </div>


        @endforelse

    </div>

</div>
@extends('layouts.admin')

@section('content')

<div class="content-header">
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-3">

            <h1>Photo Tasks</h1>

        </div>

        <div class="card">

            <div class="card-body p-0">

                <table class="table table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>Order No</th>
                            <th>Customer</th>
                            <th>Due Date</th>
                            <th>Delivery Method</th>
                            <th>Status</th>
                            <th width="120">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($orders as $order)

                        <tr>

                            <td>{{ $order->order_no }}</td>

                            <td>{{ $order->customer->customer_name }}</td>

                            <td>

                                {{ \Carbon\Carbon::parse($order->due_date)->format('d/m/Y') }}

                            </td>

                            <td>

                                {{ $order->delivery_method }}

                            </td>

                            <td>

                               <span class="badge {{ $order->getStatusBadgeClass() }}">

    {{ $order->getStatusBadgeText() }}

</span>

                            </td>

                            <td>

                                <a href="{{ route('cameraman.tasks.show',$order) }}"
                                    class="btn btn-primary btn-sm">

                                    <i class="fas fa-camera"></i>

                                        Open

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6" class="text-center">

                                No photo task available.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="mt-3">

            {{ $orders->links() }}

        </div>

    </div>
</div>

@endsection
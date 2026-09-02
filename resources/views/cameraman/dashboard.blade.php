@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h1>

                Cameraman Dashboard

            </h1>

            <a href="{{ route('cameraman.tasks') }}"
               class="btn btn-primary">

                <i class="fas fa-camera"></i>

                My Tasks

            </a>

        </div>

        <div class="row">

            <div class="col-lg-3 col-6">

                <div class="small-box bg-warning">

                    <div class="inner">

                        <h3>{{ $ready }}</h3>

                        <p>Ready for Photo</p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-box"></i>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-6">

                <div class="small-box bg-info">

                    <div class="inner">

                        <h3>{{ $inProgress }}</h3>

                        <p>Photo Session</p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-camera"></i>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-6">

                <div class="small-box bg-success">

                    <div class="inner">

                        <h3>{{ $completedToday }}</h3>

                        <p>Completed Today</p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-check-circle"></i>

                    </div>

                </div>

            </div>

            <div class="col-lg-3 col-6">

                <div class="small-box bg-dark">

                    <div class="inner">

                        <h3>{{ $totalCompleted }}</h3>

                        <p>Total Completed</p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-images"></i>

                    </div>

                </div>

            </div>

        </div>

        <div class="card mt-3">

            <div class="card-header">

                <strong>Recent Photo Tasks</strong>

            </div>

            <div class="card-body">

                <table class="table table-bordered table-hover">

                    <thead>

                        <tr>

                            <th>Order No</th>

                            <th>Customer</th>

                            <th>Due Date</th>

                            <th>Status</th>

                            <th width="120">Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($recentTasks as $order)

                        <tr>

                            <td>{{ $order->order_no }}</td>

                            <td>{{ $order->customer->customer_name }}</td>

                            <td>{{ \Carbon\Carbon::parse($order->due_date)->format('d/m/Y') }}</td>

                            <td>

                                @if($order->status == 'Ready at HQ')

                                    <span class="badge bg-warning">

                                        Ready at HQ

                                    </span>

                                @else

                                    <span class="badge bg-info">

                                        Photo Session

                                    </span>

                                @endif

                            </td>

                            <td>

                                <a href="{{ route('cameraman.tasks.show',$order) }}"
                                   class="btn btn-primary btn-sm">

                                    Open

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5"
                                class="text-center">

                                No task available.

                            </td>

                        </tr>

                    @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection
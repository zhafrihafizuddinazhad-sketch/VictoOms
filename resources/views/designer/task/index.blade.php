@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <h1>My Tasks</h1>

            <span class="badge bg-primary p-2">

                {{ $orders->count() }} Active Task(s)

            </span>

        </div>

        {{-- Statistics --}}
        <div class="row mb-4">

            <div class="col-md-4">

                <div class="small-box bg-primary">

                    <div class="inner">

                        <h3>{{ $assigned }}</h3>

                        <p>Assigned</p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-list-check"></i>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="small-box bg-warning">

                    <div class="inner">

                        <h3>{{ $inProgress }}</h3>

                        <p>In Progress</p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-pen-ruler"></i>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="small-box bg-success">

                    <div class="inner">

                        <h3>{{ $pendingApproval }}</h3>

                        <p>Pending Approval</p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-check-circle"></i>

                    </div>

                </div>

            </div>

        </div>

        {{-- Search --}}
        <div class="card mb-3">

            <div class="card-body">

                <form method="GET">

                    <div class="row">

                        <div class="col-md-5">

                            <input
                                type="text"
                                name="search"
                                value="{{ request('search') }}"
                                class="form-control"
                                placeholder="Search Order No or Customer">

                        </div>

                        <div class="col-md-3">

                            <select
                                name="status"
                                class="form-select">

                                <option value="">All Status</option>

                                <option value="Assigned"
                                    {{ request('status') == 'Assigned' ? 'selected' : '' }}>
                                    Assigned
                                </option>

                                <option value="In Progress"
                                    {{ request('status') == 'In Progress' ? 'selected' : '' }}>
                                    In Progress
                                </option>

                                <option value="Pending Approval"
                                    {{ request('status') == 'Pending Approval' ? 'selected' : '' }}>
                                    Pending Approval
                                </option>

                                <option value="Printing"
                                    {{ request('status') == 'Printing' ? 'selected' : '' }}>
                                    Printing
                                </option>

                                <option value="Completed"
                                    {{ request('status') == 'Completed' ? 'selected' : '' }}>
                                    Completed
                                </option>

                            </select>

                        </div>

                        <div class="col-md-4">

                            <button class="btn btn-primary">

                                <i class="fas fa-search"></i>

                                Search

                            </button>

                            <a
                                href="{{ route('designer.task') }}"
                                class="btn btn-secondary">

                                Reset

                            </a>

                        </div>

                    </div>

                </form>

            </div>

        </div>

        {{-- Tasks --}}
        <div class="card">

            <div class="card-header">

                <strong>Assigned Orders</strong>

            </div>

            <div class="card-body p-0">

                <table class="table table-hover table-bordered mb-0">

                    <thead class="table-light">

                        <tr>

                            <th>Order No</th>

                            <th>Customer</th>

                            <th width="180">Due Date</th>

                            <th width="180">Status</th>

                            <th width="120" class="text-center">

                                Action

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    @forelse($orders as $order)

                        @php

                            $due = \Carbon\Carbon::parse($order->due_date);

                        @endphp

                        <tr>

                            <td>

                                <strong>{{ $order->order_no }}</strong>

                            </td>

                            <td>

                                {{ $order->customer->customer_name }}

                            </td>

                            <td>

                                {{ $due->format('d/m/Y') }}

                                <br>

                                @if($due->lt(today()))

                                    <small class="text-danger">

                                        <i class="fas fa-circle"></i>

                                        Overdue

                                    </small>

                                @elseif($due->isToday())

                                    <small class="text-warning">

                                        <i class="fas fa-clock"></i>

                                        Due Today

                                    </small>

                                @else

                                    <small class="text-success">

                                        {{ intval(now()->diffInDays($due)) }} day(s) left

                                    </small>

                                @endif

                            </td>

                            <td>

                                <span class="badge {{ $order->getStatusBadgeClass() }}">

                                    {{ $order->getStatusBadgeText() }}

                                </span>

                            </td>

                            <td class="text-center">

                                @if($order->status == 'Assigned')

                                    <form
                                        action="{{ route('designer.task.start',$order) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf
                                        @method('PATCH')

                                        <button
                                            class="btn btn-primary btn-sm"
                                            title="Start Task">

                                            <i class="fas fa-play"></i>

                                        </button>

                                    </form>

                                @elseif($order->status == 'In Progress')

                                    <a
                                        href="{{ route('designer.task.show',$order) }}"
                                        class="btn btn-warning btn-sm"
                                        title="Continue Task">

                                        <i class="fas fa-pencil-alt"></i>

                                    </a>

                                @else

                                    <a
                                        href="{{ route('designer.task.show',$order) }}"
                                        class="btn btn-info btn-sm"
                                        title="View Task">

                                        <i class="fas fa-eye"></i>

                                    </a>

                                @endif

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="5" class="text-center py-5">

                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>

                                <br>

                                No task assigned.

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
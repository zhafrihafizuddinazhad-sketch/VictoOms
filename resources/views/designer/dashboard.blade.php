@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="container-fluid">


        {{-- ================================================= --}}
        {{-- HEADER --}}
        {{-- ================================================= --}}

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="mb-1">

                    Welcome back, {{ auth()->user()->name }} 👋

                </h1>

                <p class="text-muted mb-0">

                    Here's what's happening with your design tasks today.

                </p>

            </div>


            <a
                href="{{ route('designer.task') }}"
                class="btn btn-primary">

                <i class="fas fa-tasks mr-1"></i>

                My Tasks

            </a>

        </div>


        {{-- ================================================= --}}
        {{-- STATISTICS --}}
        {{-- ================================================= --}}

        <div class="row">


            {{-- CURRENT TASKS --}}

            <div class="col-lg-3 col-6">

                <div class="small-box bg-info">

                    <div class="inner">

                        <h3>

                            {{ $currentTasks }}

                        </h3>

                        <p>

                            Current Tasks

                        </p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-tasks"></i>

                    </div>

                </div>

            </div>


            {{-- PENDING APPROVAL --}}

            <div class="col-lg-3 col-6">

                <div class="small-box bg-warning">

                    <div class="inner">

                        <h3>

                            {{ $pendingApproval }}

                        </h3>

                        <p>

                            Pending Approval

                        </p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-paper-plane"></i>

                    </div>

                </div>

            </div>


            {{-- COMPLETED --}}

            <div class="col-lg-3 col-6">

                <div class="small-box bg-success">

                    <div class="inner">

                        <h3>

                            {{ $completedTasks }}

                        </h3>

                        <p>

                            Completed

                        </p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-check-circle"></i>

                    </div>

                </div>

            </div>


            {{-- OVERDUE --}}

            <div class="col-lg-3 col-6">

                <div class="small-box bg-danger">

                    <div class="inner">

                        <h3>

                            {{ $overdueTasks }}

                        </h3>

                        <p>

                            Overdue

                        </p>

                    </div>

                    <div class="icon">

                        <i class="fas fa-exclamation-circle"></i>

                    </div>

                </div>

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- TODAY'S PRIORITY --}}
        {{-- ================================================= --}}

        <div class="card mt-4">


            <div class="card-header bg-danger text-white">

                <h3 class="card-title">

                    <i class="fas fa-fire mr-1"></i>

                    Today's Priority

                </h3>

            </div>


            <div class="card-body">


                @if($todayPriority)

                    <div class="row align-items-center">


                        <div class="col-md-8">

                            <h4 class="mb-2">

                                {{ $todayPriority->order_no }}

                            </h4>


                            <p class="mb-1">

                                <strong>Customer:</strong>

                                {{ $todayPriority->customer->customer_name }}

                            </p>


                            <p class="mb-1">

                                <strong>Due Date:</strong>

                                {{ \Carbon\Carbon::parse($todayPriority->due_date)->format('d/m/Y') }}

                            </p>


                            <p class="mb-0">

                                <strong>Status:</strong>

                                <span class="badge bg-warning">

                                    {{ $todayPriority->status }}

                                </span>

                            </p>

                        </div>


                        <div class="col-md-4 text-md-right mt-3 mt-md-0">

                            <a
                                href="{{ route('designer.task.show', $todayPriority) }}"
                                class="btn btn-primary">

                                <i class="fas fa-play mr-1"></i>

                                Continue Working

                            </a>

                        </div>

                    </div>


                @else

                    <div class="alert alert-success mb-0">

                        <i class="fas fa-check-circle mr-1"></i>

                        🎉 No urgent task today.

                    </div>

                @endif

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- RECENT TASKS --}}
        {{-- ================================================= --}}

        <div class="card mt-4">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-clock mr-1"></i>

                    Recent Assigned Tasks

                </h3>

            </div>


            <div class="card-body p-0">

                <div class="table-responsive">

                    <table class="table table-hover mb-0">

                        <thead>

                            <tr>

                                <th>Order No</th>

                                <th>Customer</th>

                                <th>Status</th>

                                <th>Due Date</th>

                                <th width="120">Action</th>

                            </tr>

                        </thead>


                        <tbody>


                            @forelse($recentTasks as $task)

                                <tr>


                                    <td>

                                        <strong>

                                            {{ $task->order_no }}

                                        </strong>

                                    </td>


                                    <td>

                                        {{ $task->customer->customer_name }}

                                    </td>


                                    <td>

                                        @php

                                            $statusClass = match($task->status) {

                                                'Assigned' => 'bg-info',

                                                'In Progress' => 'bg-primary',

                                                'Pending Approval' => 'bg-warning',

                                                'Completed' => 'bg-success',

                                                default => 'bg-secondary',

                                            };

                                        @endphp


                                        <span class="badge {{ $statusClass }}">

                                            {{ $task->status }}

                                        </span>

                                    </td>


                                    <td>

                                        {{ \Carbon\Carbon::parse($task->due_date)->format('d/m/Y') }}

                                    </td>


                                    <td>

                                        <a
                                            href="{{ route('designer.task.show', $task) }}"
                                            class="btn btn-info btn-sm">

                                            <i class="fas fa-eye"></i>

                                            View

                                        </a>

                                    </td>

                                </tr>


                            @empty

                                <tr>

                                    <td
                                        colspan="5"
                                        class="text-center py-4">

                                        <i class="fas fa-inbox fa-2x text-muted mb-2"></i>

                                        <div>

                                            No task assigned.

                                        </div>

                                    </td>

                                </tr>

                            @endforelse


                        </tbody>

                    </table>

                </div>

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- QUICK ACTIONS --}}
        {{-- ================================================= --}}

        <div class="card mt-4">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-bolt mr-1"></i>

                    Quick Actions

                </h3>

            </div>


            <div class="card-body">

                <a
                    href="{{ route('designer.task') }}"
                    class="btn btn-primary mr-2">

                    <i class="fas fa-tasks mr-1"></i>

                    View My Tasks

                </a>


                <a
                    href="{{ route('notifications.index') }}"
                    class="btn btn-outline-secondary">

                    <i class="fas fa-bell mr-1"></i>

                    Notifications

                </a>

            </div>

        </div>

    </div>

</div>

@endsection
@extends('layouts.admin')

@section('content')

@php

$progress = match($order->status) {

    'Pending' => 0,

    'Assigned' => 15,

    'In Progress' => 30,

    'Pending Approval' => 40,

    'Printing' => 50,

    'Ready at HQ' => 60,

    'Photo Session' => 70,

    'Photo Completed' => 80,

    'Out for Delivery' => 90,

    'Waiting for Pickup' => 90,

    'Completed' => 100,

    default => 0,

};

$due = \Carbon\Carbon::parse($order->due_date);

$daysLeft = max(
    0,
    (int) ceil(now()->floatDiffInDays($due))
);

@endphp


<div class="container-fluid">

    <div class="card">

        <div class="card-header">

            <h3 class="mb-0">

                Designer Workspace

            </h3>

        </div>


        <div class="card-body">


            {{-- ================================================= --}}
            {{-- KPI --}}
            {{-- ================================================= --}}

            <div class="row mb-4">

                <div class="col-md-3">

                    <div class="small-box bg-info">

                        <div class="inner">

                            <h3>{{ $order->status }}</h3>

                            <p>Current Status</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-tasks"></i>

                        </div>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="small-box bg-success">

                        <div class="inner">

                            <h3>{{ $progress }}%</h3>

                            <p>Progress</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-chart-line"></i>

                        </div>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="small-box bg-warning">

                        <div class="inner">

                            <h3>{{ $due->format('d M') }}</h3>

                            <p>

                                @if($due->isPast())

                                    Overdue

                                @elseif($due->isToday())

                                    Due Today

                                @elseif($due->isTomorrow())

                                    Due Tomorrow

                                @else

                                    Due Date

                                @endif

                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-calendar-alt"></i>

                        </div>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="small-box bg-primary">

                        <div class="inner">

                            <h3>{{ $order->items->count() }}</h3>

                            <p>Products</p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-box"></i>

                        </div>

                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- ORDER INFORMATION --}}
            {{-- ================================================= --}}

            <div class="card mb-4">

                <div class="card-header">

                    <strong>Order Information</strong>

                </div>


                <div class="card-body">

                    <table class="table table-bordered mb-0">

                        <tr>

                            <th width="220">

                                Order No

                            </th>

                            <td>

                                {{ $order->order_no }}

                            </td>

                        </tr>


                        <tr>

                            <th>Status</th>

                            <td>

                                <span class="badge {{ $order->getStatusBadgeClass() }}">

                                    {{ $order->getStatusBadgeText() }}

                                </span>

                            </td>

                        </tr>


                        <tr>

                            <th>Due Date</th>

                            <td>

                                {{ $due->format('d M Y') }}

                            </td>

                        </tr>


                        <tr>

                            <th>Days Left</th>

                            <td>

                                @if($due->isPast())

                                    <span class="badge bg-danger">

                                        Overdue

                                    </span>

                                @elseif($due->isToday())

                                    <span class="badge bg-warning">

                                        Due Today

                                    </span>

                                @elseif($due->isTomorrow())

                                    <span class="badge bg-info">

                                        Due Tomorrow

                                    </span>

                                @else

                                    <span class="badge bg-success">

                                        {{ $daysLeft }} day(s) left

                                    </span>

                                @endif

                            </td>

                        </tr>

                    </table>

                </div>

            </div>



            {{-- ================================================= --}}
{{-- CUSTOMER INFORMATION --}}
{{-- ================================================= --}}

<div class="card mb-4">

    <div class="card-header">

        <strong>

            <i class="fas fa-user mr-1"></i>

            Customer Information

        </strong>

    </div>


    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <strong>Customer Name</strong>

                <p class="text-muted mb-3">

                    {{ $order->customer->customer_name ?? '-' }}

                </p>

            </div>


            <div class="col-md-6">

                <strong>Company</strong>

                <p class="text-muted mb-3">

                    {{ $order->customer->company_name ?? '-' }}

                </p>

            </div>


            <div class="col-md-6">

                <strong>Phone</strong>

                <p class="text-muted mb-3">

                    {{ $order->customer->phone ?? '-' }}

                </p>

            </div>


            <div class="col-md-6">

                <strong>Email</strong>

                <p class="text-muted mb-3">

                    {{ $order->customer->email ?? '-' }}

                </p>

            </div>

        </div>

    </div>

</div>



{{-- ================================================= --}}
{{-- CUSTOMER BRIEF --}}
{{-- ================================================= --}}

<div class="card mb-4">

    <div class="card-header">

        <strong>
            <i class="fas fa-align-left mr-1"></i>
            Customer Brief
        </strong>

    </div>

    <div class="card-body">

        @if($order->customer_brief)

            <div
                class="p-3 bg-light rounded"
                style="white-space: pre-line;"
            >
                {{ $order->customer_brief }}
            </div>

        @else

            <p class="text-muted mb-0">
                No customer brief was provided.
            </p>

        @endif

    </div>

</div>


{{-- ================================================= --}}
{{-- CUSTOMER REFERENCES --}}
{{-- ================================================= --}}

<div class="card mb-4">

    <div class="card-header">

        <strong>
            <i class="fas fa-paperclip mr-1"></i>
            Customer References
        </strong>

    </div>

    <div class="card-body">

        @if($order->references && $order->references->count())

            <div class="row">

                @foreach($order->references as $reference)

                    <div class="col-md-6 mb-3">

                        <div class="border rounded p-3 h-100">

                            {{-- FILE --}}
                            @if($reference->file_path)

                                <div class="d-flex align-items-center">

                                    <i class="fas fa-file-alt fa-2x mr-3"></i>

                                    <div>

                                        <strong>
                                            {{ $reference->file_name ?? 'Reference File' }}
                                        </strong>

                                        @if($reference->file_extension)

                                            <div class="text-muted small">

                                                {{ strtoupper($reference->file_extension) }}

                                            </div>

                                        @endif

                                    </div>

                                </div>


                                <div class="mt-3">

                                    <a
                                        href="{{ asset('storage/' . $reference->file_path) }}"
                                        target="_blank"
                                        class="btn btn-sm btn-primary"
                                    >

                                        <i class="fas fa-eye mr-1"></i>

                                        View File

                                    </a>

                                </div>

                            {{-- LINK --}}
                            @elseif($reference->reference_link)

                                <div class="d-flex align-items-center">

                                    <i class="fas fa-link fa-2x mr-3"></i>

                                    <div class="text-truncate">

                                        <strong>
                                            Reference Link
                                        </strong>

                                        <div class="text-muted small text-truncate">

                                            {{ $reference->reference_link }}

                                        </div>

                                    </div>

                                </div>


                                <div class="mt-3">

                                    <a
                                        href="{{ $reference->reference_link }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn btn-sm btn-primary"
                                    >

                                        <i class="fas fa-external-link-alt mr-1"></i>

                                        Open Link

                                    </a>

                                </div>

                            @endif

                        </div>

                    </div>

                @endforeach

            </div>

        @else

            <p class="text-muted mb-0">

                No customer references were provided.

            </p>

        @endif

    </div>

</div>

            {{-- ================================================= --}}
            {{-- REPEAT ORDER INFORMATION --}}
            {{-- ================================================= --}}

            @if($order->is_repeat_order && $order->originalOrder)

                <div class="card mb-4 border-info">

                    <div class="card-header bg-info">

                        <strong>

                            <i class="fas fa-sync-alt mr-1"></i>

                            Repeat Order

                        </strong>

                    </div>


                    <div class="card-body">


                        {{-- Same Design --}}

                        @if($order->repeat_type === 'same_design')

                            <div class="alert alert-success">

                                <h5>

                                    <i class="fas fa-check-circle mr-1"></i>

                                    Same Design Repeat Order

                                </h5>

                                <p class="mb-0">

                                    This order uses the same design as the
                                    original order.

                                    <strong>
                                        No redesign is required.
                                    </strong>

                                </p>

                            </div>


                        {{-- Minor Changes --}}

                        @elseif($order->repeat_type === 'minor_changes')

                            <div class="alert alert-warning">

                                <h5>

                                    <i class="fas fa-edit mr-1"></i>

                                    Minor Changes Repeat Order

                                </h5>

                                <p class="mb-0">

                                    Use the original design as a reference
                                    and upload the revised design when ready.

                                </p>

                            </div>

                        @endif


                        <div class="row mb-3">

                            <div class="col-md-4">

                                <strong>

                                    Original Order

                                </strong>

                                <p class="mb-0">

                                    {{ $order->originalOrder->order_no }}

                                </p>

                            </div>


                            <div class="col-md-4">

                                <strong>

                                    Repeat Type

                                </strong>

                                <p class="mb-0">

                                    {{ ucwords(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $order->repeat_type
                                        )
                                    ) }}

                                </p>

                            </div>


                            <div class="col-md-4">

                                <strong>

                                    Original Designer

                                </strong>

                                <p class="mb-0">

                                    {{ $order->originalOrder->designer->name ?? '-' }}

                                </p>

                            </div>

                        </div>


                        {{-- ================================================= --}}
                        {{-- ORIGINAL DESIGN --}}
                        {{-- ================================================= --}}

                        @if(
                            $order->originalOrder->designFiles &&
                            $order->originalOrder->designFiles->count()
                        )

                            <hr>


                            <h5>

                                <i class="fas fa-palette mr-1"></i>

                                Original Design

                            </h5>


                            @if($order->repeat_type === 'same_design')

                                <p class="text-muted">

                                    This is the design used for the original
                                    order. No new design upload is required
                                    for this repeat order.

                                </p>

                            @elseif($order->repeat_type === 'minor_changes')

                                <p class="text-muted">

                                    Use the following design as a reference
                                    before preparing the revised design.

                                </p>

                            @endif


                            <div class="table-responsive">

                                <table class="table table-bordered table-hover">

                                    <thead class="table-light">

                                        <tr>

                                            <th width="100">

                                                Version

                                            </th>

                                            <th>

                                                File

                                            </th>

                                            <th width="180">

                                                Uploaded By

                                            </th>

                                            <th width="180">

                                                Uploaded At

                                            </th>

                                            <th width="170">

                                                Action

                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        @foreach(
                                            $order->originalOrder
                                                ->designFiles
                                                ->sortByDesc('version')
                                            as $designFile
                                        )

                                            <tr>

                                                <td>

                                                    <span class="badge bg-primary">

                                                        V{{ $designFile->version }}

                                                    </span>

                                                </td>


                                                <td>

                                                    {{ $designFile->file_name }}

                                                </td>


                                                <td>

                                                    {{ $designFile->uploader->name ?? '-' }}

                                                </td>


                                                <td>

                                                    {{ $designFile->created_at
                                                        ->format('d M Y h:i A') }}

                                                </td>


                                                <td>

                                                    @if(
                                                        in_array(
                                                            strtolower(
                                                                $designFile->file_extension
                                                            ),
                                                            [
                                                                'jpg',
                                                                'jpeg',
                                                                'png',
                                                                'pdf'
                                                            ]
                                                        )
                                                    )

                                                        <a
                                                            href="{{ route(
                                                                'designs.preview',
                                                                $designFile
                                                            ) }}"
                                                            target="_blank"
                                                            class="btn btn-info btn-sm">

                                                            <i class="fas fa-eye"></i>

                                                            View

                                                        </a>

                                                    @endif


                                                    <a
                                                        href="{{ route(
                                                            'designs.download',
                                                            $designFile
                                                        ) }}"
                                                        class="btn btn-primary btn-sm">

                                                        <i class="fas fa-download"></i>

                                                        Download

                                                    </a>

                                                </td>

                                            </tr>

                                        @endforeach

                                    </tbody>

                                </table>

                            </div>

                        @else

                            <hr>

                            <div class="alert alert-warning mb-0">

                                <i class="fas fa-exclamation-triangle mr-1"></i>

                                No original design file was found.

                            </div>

                        @endif

                    </div>

                </div>

            @endif


            {{-- ================================================= --}}
            {{-- DESIGNER ACTION --}}
            {{-- ================================================= --}}

            <div class="card mb-4">

                <div class="card-header">

                    <strong>Designer Action</strong>

                </div>


                <div class="card-body">

                    @switch($order->status)


                        {{-- ASSIGNED --}}

                        @case('Assigned')

                            <div class="alert alert-info">

                                <h5>

                                    <i class="fas fa-play-circle"></i>

                                    Task Not Started

                                </h5>

                                Click

                                <strong>

                                    Start Task

                                </strong>

                                to unlock the workspace.

                            </div>


                            <form
                                action="{{ route(
                                    'designer.task.start',
                                    $order
                                ) }}"
                                method="POST">

                                @csrf

                                @method('PATCH')


                                <button class="btn btn-primary btn-lg">

                                    <i class="fas fa-play"></i>

                                    Start Task

                                </button>

                            </form>

                        @break


                        {{-- IN PROGRESS --}}

                        @case('In Progress')

                            @if($order->owner_review_comment)

                                <div class="alert alert-danger">

                                    <h5>

                                        <i class="fas fa-exclamation-triangle mr-1"></i>

                                        Revision Required

                                    </h5>

                                    <p class="mb-2">

                                        The owner has requested changes
                                        to your design.

                                    </p>

                                    <hr>

                                    <strong>

                                        Owner Feedback:

                                    </strong>

                                    <div class="mt-2">

                                        {!! nl2br(
                                            e($order->owner_review_comment)
                                        ) !!}

                                    </div>

                                </div>

                            @elseif(
                                $order->is_repeat_order &&
                                $order->repeat_type === 'same_design'
                            )

                                <div class="alert alert-success">

                                    <h5>

                                        <i class="fas fa-check-circle mr-1"></i>

                                        Same Design

                                    </h5>

                                    <p class="mb-0">

                                        No redesign is required.

                                        Proceed with the new Job Order
                                        for this repeat order.

                                    </p>

                                </div>

                            @elseif(
                                $order->is_repeat_order &&
                                $order->repeat_type === 'minor_changes'
                            )

                                <div class="alert alert-warning">

                                    <h5>

                                        <i class="fas fa-edit mr-1"></i>

                                        Minor Changes Required

                                    </h5>

                                    <p class="mb-0">

                                        Use the original design above as
                                        your reference and prepare the
                                        revised design.

                                    </p>

                                </div>

                            @else

                                <div class="alert alert-primary">

                                    <h5>

                                        <i class="fas fa-paint-brush mr-1"></i>

                                        Task In Progress

                                    </h5>

                                    <p class="mb-0">

                                        Continue working on the design and
                                        upload the latest version when ready.

                                    </p>

                                </div>

                            @endif

                        @break


                        {{-- PENDING APPROVAL --}}

                        @case('Pending Approval')

                            <div class="alert alert-warning">

                                Waiting for Owner Approval

                            </div>

                        @break


                        {{-- PRINTING --}}

                        @case('Printing')

                            <div class="alert alert-info">

                                Design Approved. Printing in progress.

                            </div>

                        @break


                        {{-- COMPLETED --}}

                        @case('Completed')

                            <div class="alert alert-success">

                                Order Completed.

                            </div>

                        @break


                    @endswitch

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- CUSTOMER --}}
            {{-- ================================================= --}}

            <div class="card mb-4">

                <div class="card-header">

                    <strong>Customer Information</strong>

                </div>


                <div class="card-body">

                    <table class="table table-bordered mb-0">

                        <tr>

                            <th width="220">

                                Customer Name

                            </th>

                            <td>

                                {{ $order->customer->customer_name }}

                            </td>

                        </tr>


                        <tr>

                            <th>

                                Remarks

                            </th>

                            <td>

                                {{ $order->remarks ?? '-' }}

                            </td>

                        </tr>

                    </table>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- PRODUCT LIST --}}
            {{-- ================================================= --}}

            <div class="card mb-4">

                <div class="card-header">

                    <strong>Product List</strong>

                </div>


                <div class="card-body">

                    <table class="table table-bordered">

                        <thead>

                            <tr>

                                <th>

                                    Product

                                </th>

                                <th width="150">

                                    Qty

                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @forelse($order->items as $item)

                                <tr>

                                    <td>

                                        {{ $item->product_name }}

                                    </td>

                                    <td>

                                        {{ $item->quantity }}

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td
                                        colspan="2"
                                        class="text-center">

                                        No products found.

                                    </td>

                                </tr>

                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

{{-- ================================================= --}}
{{-- JOB ORDERS --}}
{{-- ================================================= --}}

<div class="card mb-4">

    <div class="card-header d-flex justify-content-between align-items-center">

        <strong>

            <i class="fas fa-clipboard-list mr-1"></i>

            Job Orders

        </strong>


        @if(
            $order->status === 'In Progress' ||
            $order->status === 'Assigned'
        )

            <a
                href="{{ route(
                    'job-orders.create',
                    $order
                ) }}"
                class="btn btn-primary btn-sm">

                <i class="fas fa-plus"></i>

                Create Job Order

            </a>

        @endif

    </div>


    <div class="card-body">


        @if($order->jobOrders->count())

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-light">

                        <tr>

                            <th>

                                Job Order No

                            </th>

                            <th>

                                Created By

                            </th>

                            <th>

                                Status

                            </th>

                            <th>

                                Created At

                            </th>

                            <th width="150">

                                Action

                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        @foreach(
                            $order->jobOrders->sortByDesc('created_at')
                            as $jobOrder
                        )

                            <tr>

                                <td>

                                    <strong>

                                        {{ $jobOrder->job_order_no }}

                                    </strong>

                                </td>


                                <td>

                                    {{ $jobOrder->creator->name ?? '-' }}

                                </td>


                                <td>

                                    @if($jobOrder->status === 'Draft')

                                        <span class="badge bg-warning text-dark">

                                            Draft

                                        </span>

                                    @else

                                        <span class="badge bg-success">

                                            {{ $jobOrder->status }}

                                        </span>

                                    @endif

                                </td>


                                <td>

                                    {{ $jobOrder->created_at
                                        ->format('d M Y h:i A') }}

                                </td>


                                <td>

    <a
        href="{{ route(
            'job-orders.generate-word',
            $jobOrder
        ) }}"
        class="btn btn-success btn-sm">

        <i class="fas fa-file-word"></i>

        Word

    </a>


    @if(
        auth()->user()->hasRole('designer') &&
        $jobOrder->created_by == auth()->id() &&
        $jobOrder->status === 'Draft'
    )

        <form
            action="{{ route(
                'job-orders.destroy',
                $jobOrder
            ) }}"
            method="POST"
            class="d-inline">

            @csrf

            @method('DELETE')


            <button
                type="submit"
                class="btn btn-danger btn-sm"
                onclick="return confirm(
                    'Are you sure you want to delete this Job Order? This action cannot be undone.'
                )">

                <i class="fas fa-trash"></i>

                Delete

            </button>

        </form>

    @endif

</td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="alert alert-secondary mb-0">

                <i class="fas fa-info-circle mr-1"></i>

                No Job Order has been created for this order yet.

            </div>

        @endif

    </div>

</div>

            {{-- ================================================= --}}
            {{-- PROGRESS --}}
            {{-- ================================================= --}}

            <div class="card mb-4">

                <div class="card-header">

                    <strong>

                        Order Progress

                    </strong>

                </div>


                <div class="card-body">

                    <div
                        class="progress"
                        style="height:25px;">

                        <div
                            class="progress-bar bg-success"
                            style="width:{{ $progress }}%">

                            {{ $progress }}%

                        </div>

                    </div>

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- ACTIVITY TIMELINE --}}
            {{-- ================================================= --}}

            <div class="card mb-4">

                <div class="card-header">

                    <strong>

                        Activity Timeline

                    </strong>

                </div>


                <div class="card-body">

                    @forelse($order->activityLogs as $log)

                        <div
                            class="border-start border-4 border-primary ps-3 mb-3">

                            <strong>

                                {{ $log->action }}

                            </strong>

                            <br>

                            {{ $log->description }}

                            <br>

                            <small class="text-muted">

                                {{ $log->created_at
                                    ->format('d M Y h:i A') }}

                            </small>

                        </div>

                    @empty

                        <div class="alert alert-secondary mb-0">

                            No activity yet.

                        </div>

                    @endforelse

                </div>

            </div>


            {{-- ================================================= --}}
            {{-- OWNER FEEDBACK --}}
            {{-- ================================================= --}}

            @if($order->owner_review_comment)

                <div class="card mb-4 border-danger">

                    <div class="card-header bg-danger text-white">

                        <strong>

                            <i class="fas fa-comment-dots"></i>

                            Owner Feedback

                        </strong>

                    </div>


                    <div class="card-body">

                        {!! nl2br(
                            e($order->owner_review_comment)
                        ) !!}


                        @if($order->reviewed_at)

                            <hr>

                            <small class="text-muted">

                                Reviewed on

                                {{ \Carbon\Carbon::parse(
                                    $order->reviewed_at
                                )->format(
                                    'd M Y h:i A'
                                ) }}

                            </small>

                        @endif

                    </div>

                </div>

            @endif


            {{-- ================================================= --}}
            {{-- DESIGN FILES --}}
            {{-- ================================================= --}}
            
            {{-- Same Design = NO upload --}}
            {{-- Minor Changes = upload --}}
            {{-- Normal Order = upload --}}
            

            @if(
                $order->status == 'In Progress' &&
                (
                    !$order->is_repeat_order ||
                    $order->repeat_type === 'minor_changes'
                )
            )

                <div class="card mb-4">

                    <div class="card-header">

                        <strong>

                            @if(
                                $order->is_repeat_order &&
                                $order->repeat_type === 'minor_changes'
                            )

                                Revised Design

                            @else

                                Design Files

                            @endif

                        </strong>

                    </div>


                    <div class="card-body">

                        <form
    action="{{ route(
        'designer.designs.store',
        $order
    ) }}"
    method="POST"
    enctype="multipart/form-data">

    @csrf


    {{-- ================================================= --}}
    {{-- DESIGN FILE UPLOAD --}}
    {{-- ================================================= --}}

    <div class="mb-3">

        <label class="form-label">

            @if(
                $order->is_repeat_order &&
                $order->repeat_type === 'minor_changes'
            )

                Upload Revised Design

            @else

                Choose Design File

            @endif

        </label>


        {{-- Minor Changes Notice --}}

        @if(
            $order->is_repeat_order &&
            $order->repeat_type === 'minor_changes'
        )

            <div class="alert alert-warning">

                <i class="fas fa-info-circle mr-1"></i>

                Use the original design above as
                your reference and upload the
                revised design here.

            </div>

        @endif


        {{-- Hidden real input --}}

        <input
            type="file"
            name="design_files[]"
            id="design_file"
            class="d-none"
            multiple
            accept=".jpg,.jpeg,.png,.pdf,.ai,.eps,.svg,.psd,.cdr,.otf,.ttf"
        >


        {{-- Drag & Drop Area --}}

        <div
            id="designDropZone"
            tabindex="0"
            class="border rounded text-center p-4"
            style="
                border: 2px dashed #adb5bd !important;
                background-color: #f8f9fa;
                cursor: pointer;
                transition: all 0.2s ease;
            "
        >

            <div class="mb-3">

                <i
                    class="fas fa-cloud-upload-alt text-primary"
                    style="font-size: 42px;"
                ></i>

            </div>


            <h5 class="mb-2">

                Drag & Drop your design file here

            </h5>


            <p class="text-muted mb-2">

                or

                <span class="text-primary font-weight-bold">

                    click here to browse

                </span>

            </p>


            <div class="text-muted">

                <i class="fas fa-paste mr-1"></i>

                You can also paste an image with

                <strong>Ctrl + V</strong>

            </div>


            <small class="d-block text-muted mt-3">
                Supported:
                JPG, PNG, PDF, AI, EPS, SVG, PSD, CDR, OTF, TTF
            </small>

        </div>


        {{-- Selected File --}}

        <div
            id="selectedDesignFile"
            class="mt-3"
        >

            <div class="alert alert-light text-muted mb-0">

                <i class="fas fa-file mr-1"></i>

                No design file selected.

            </div>

        </div>

    </div>


    {{-- ================================================= --}}
    {{-- VERSION NOTE --}}
    {{-- ================================================= --}}

    <div class="mb-3">

        <label class="form-label">

            Version Note

        </label>


        <textarea
            name="remarks"
            class="form-control"
            rows="3"
            placeholder="{{ 
                $order->is_repeat_order &&
                $order->repeat_type === 'minor_changes'
                    ? 'Example: Revised from ORD-' . $order->originalOrder->order_no
                    : 'Example: Initial Design'
            }}"
        ></textarea>

    </div>


    {{-- ================================================= --}}
    {{-- SUBMIT --}}
    {{-- ================================================= --}}

    <button
        type="submit"
        class="btn btn-primary"
        id="uploadDesignButton"
    >

        <i class="fas fa-upload mr-1"></i>

        @if(
            $order->is_repeat_order &&
            $order->repeat_type === 'minor_changes'
        )

            Upload Revised Design

        @else

            Upload Design

        @endif

    </button>

</form>


                        <hr>


                        <h5 class="mb-3">

                            Uploaded Designs

                        </h5>


                        <div class="table-responsive">

                            <table
                                class="table table-bordered table-hover">

                                <thead class="table-light">

                                    <tr>

                                        <th width="80">

                                            Version

                                        </th>

                                        <th>

                                            File

                                        </th>

                                        <th width="150">

                                            Uploaded By

                                        </th>

                                        <th width="180">

                                            Uploaded At

                                        </th>

                                        <th>

                                            Remarks

                                        </th>

                                        <th width="160">

                                            Action

                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    @forelse(
                                        $order->designFiles
                                            ->sortByDesc('version')
                                        as $file
                                    )

                                        <tr>

                                            <td>

                                                <span class="badge bg-primary">

                                                    V{{ $file->version }}

                                                </span>

                                            </td>


                                            <td>

                                                {{ $file->file_name }}

                                            </td>


                                            <td>

                                                {{ $file->uploader->name ?? '-' }}

                                            </td>


                                            <td>

                                                {{ $file->created_at
                                                    ->format(
                                                        'd M Y h:i A'
                                                    ) }}

                                            </td>


                                            <td>

                                                {{ $file->remarks ?? '-' }}

                                            </td>


                                            <td>

                                                @if(
                                                    in_array(
                                                        strtolower(
                                                            $file->file_extension
                                                        ),
                                                        [
                                                            'jpg',
                                                            'jpeg',
                                                            'png',
                                                            'pdf'
                                                        ]
                                                    )
                                                )

                                                    <a
                                                        href="{{ route(
                                                            'designs.preview',
                                                            $file
                                                        ) }}"
                                                        target="_blank"
                                                        class="btn btn-info btn-sm">

                                                        <i class="fas fa-eye"></i>

                                                    </a>

                                                @endif


                                                <a
                                                    href="{{ route(
                                                        'designs.download',
                                                        $file
                                                    ) }}"
                                                    class="btn btn-primary btn-sm">

                                                    <i class="fas fa-download"></i>

                                                </a>


                                                @if($order->status == 'In Progress')

                                                    <form
                                                        action="{{ route(
                                                            'designer.designs.destroy',
                                                            $file
                                                        ) }}"
                                                        method="POST"
                                                        class="d-inline"
                                                        onsubmit="return confirm('Delete this design?')">

                                                        @csrf

                                                        @method('DELETE')


                                                        <button
                                                            class="btn btn-danger btn-sm">

                                                            <i class="fas fa-trash"></i>

                                                        </button>

                                                    </form>

                                                @else

                                                    <span
                                                        class="badge bg-success">

                                                        Submitted

                                                    </span>

                                                @endif

                                            </td>

                                        </tr>

                                    @empty

                                        <tr>

                                            <td
                                                colspan="6"
                                                class="text-center text-muted">

                                                No design uploaded.

                                            </td>

                                        </tr>

                                    @endforelse

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            @endif


            {{-- ================================================= --}}
            {{-- SAME DESIGN - EXISTING DESIGN NOTICE --}}
            {{-- ================================================= --}}

            @if(
                $order->status == 'In Progress' &&
                $order->is_repeat_order &&
                $order->repeat_type === 'same_design'
            )

                <div class="card mb-4 border-success">

                    <div class="card-header bg-success text-white">

                        <strong>

                            <i class="fas fa-check-circle mr-1"></i>

                            Design Confirmation

                        </strong>

                    </div>


                    <div class="card-body">

                        <p class="mb-2">

                            This repeat order uses the original design.

                            <strong>

                                No new design file is required.

                            </strong>

                        </p>

                        <p class="mb-0 text-muted">

                            Proceed with creating the new Job Order
                            for this repeat order.

                        </p>

                    </div>

                </div>

            @endif


            {{-- ================================================= --}}
            {{-- SUBMIT --}}
            {{-- ================================================= --}}

            @if($order->status == 'In Progress')

                <div class="card mb-4">

                    <div class="card-header">

                        <strong>

                            Submit Design

                        </strong>

                    </div>


                    <div class="card-body">

                        <div class="alert alert-info">

                            Once submitted, the design will be sent to
                            the owner for review.

                        </div>


                        <form
                            action="{{ route(
                                'designer.tasks.submit',
                                $order
                            ) }}"
                            method="POST">

                            @csrf

                            @method('PATCH')


                            <button
                                class="btn btn-success"
                                onclick="return confirm(
                                    'Submit this design for approval?'
                                )">

                                <i class="fas fa-paper-plane"></i>

                                Submit For Approval

                            </button>

                        </form>

                    </div>

                </div>

            @endif


        </div>

    </div>

</div>

<script>

document.addEventListener('DOMContentLoaded', function () {

    const input = document.getElementById('design_file');
    const dropZone = document.getElementById('designDropZone');
    const selectedContainer = document.getElementById('selectedDesignFile');

    if (!input || !dropZone || !selectedContainer) {
        console.error('Design upload elements not found.');
        return;
    }


    // =========================================================
    // STORE ALL SELECTED FILES
    // =========================================================

    let selectedFiles = [];


    // =========================================================
    // ALLOWED EXTENSIONS
    // =========================================================

    const allowedExtensions = [
        'jpg',
        'jpeg',
        'png',
        'pdf',
        'ai',
        'eps',
        'svg',
        'psd',
        'cdr',
        'otf',
        'ttf'
    ];


    // =========================================================
    // FILE SIZE
    // =========================================================

    function formatFileSize(bytes) {

        if (bytes < 1024) {
            return bytes + ' B';
        }

        if (bytes < 1024 * 1024) {
            return (bytes / 1024).toFixed(1) + ' KB';
        }

        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }


    // =========================================================
    // ESCAPE HTML
    // =========================================================

    function escapeHtml(value) {

        const div = document.createElement('div');

        div.textContent = value;

        return div.innerHTML;
    }


    // =========================================================
    // CHECK DUPLICATE
    // =========================================================

    function isDuplicate(file) {

        return selectedFiles.some(function (existingFile) {

            return (
                existingFile.name === file.name &&
                existingFile.size === file.size &&
                existingFile.lastModified === file.lastModified
            );

        });

    }


    // =========================================================
    // ADD FILE
    // =========================================================

    function addFile(file) {

        if (!file) {
            return;
        }


        const extension = file.name
            .split('.')
            .pop()
            .toLowerCase();


        if (!allowedExtensions.includes(extension)) {

            alert(
                'Unsupported file type:\n\n' +
                file.name +
                '\n\nSupported:\n' +
                allowedExtensions.join(', ')
            );

            return;
        }


        if (isDuplicate(file)) {

            return;

        }


        selectedFiles.push(file);

    }


    // =========================================================
    // ADD MULTIPLE FILES
    // =========================================================

    function addFiles(files) {

        Array.from(files).forEach(function (file) {

            addFile(file);

        });


        syncInput();

        renderFiles();

    }


    // =========================================================
    // SYNC REAL INPUT
    // =========================================================

    function syncInput() {

        const dataTransfer = new DataTransfer();


        selectedFiles.forEach(function (file) {

            dataTransfer.items.add(file);

        });


        input.files = dataTransfer.files;

    }


    // =========================================================
    // RENDER SELECTED FILES
    // =========================================================

    function renderFiles() {

        if (selectedFiles.length === 0) {

            selectedContainer.innerHTML = `
                <div class="alert alert-light text-muted mb-0">

                    <i class="fas fa-file mr-1"></i>

                    No design files selected.

                </div>
            `;

            return;
        }


        let html = `
            <div class="card border">

                <div class="card-header bg-light">

                    <strong>

                        <i class="fas fa-paperclip mr-1"></i>

                        Selected Design Files

                    </strong>

                    <span class="badge badge-primary float-right">

                        ${selectedFiles.length}

                    </span>

                </div>

                <div class="list-group list-group-flush">
        `;


        selectedFiles.forEach(function (file, index) {

            html += `
                <div class="list-group-item">

                    <div class="d-flex justify-content-between align-items-center">

                        <div
                            class="d-flex align-items-center"
                            style="min-width: 0;">

                            <i
                                class="fas fa-file mr-3 text-primary"
                                style="font-size: 22px;">
                            </i>

                            <div style="min-width: 0;">

                                <strong
                                    class="d-block text-truncate">

                                    ${escapeHtml(file.name)}

                                </strong>

                                <small class="text-muted">

                                    ${formatFileSize(file.size)}

                                </small>

                            </div>

                        </div>


                        <button
                            type="button"
                            class="btn btn-sm btn-outline-danger removeDesignFile"
                            data-index="${index}">

                            <i class="fas fa-times"></i>

                        </button>

                    </div>

                </div>
            `;

        });


        html += `
                </div>

            </div>
        `;


        selectedContainer.innerHTML = html;

    }


    // =========================================================
    // CLICK
    // =========================================================

    dropZone.addEventListener('click', function (event) {

        event.preventDefault();

        input.click();

    });


    // =========================================================
    // FILE PICKER
    // =========================================================

    input.addEventListener('change', function () {

        if (this.files && this.files.length > 0) {

            addFiles(this.files);

        }

    });


    // =========================================================
    // DRAG ENTER
    // =========================================================

    dropZone.addEventListener('dragenter', function (event) {

        event.preventDefault();

        event.stopPropagation();

        this.style.borderColor = '#007bff';

        this.style.backgroundColor = '#eaf3ff';

    });


    // =========================================================
    // DRAG OVER
    // =========================================================

    dropZone.addEventListener('dragover', function (event) {

        event.preventDefault();

        event.stopPropagation();

        event.dataTransfer.dropEffect = 'copy';

        this.style.borderColor = '#007bff';

        this.style.backgroundColor = '#eaf3ff';

    });


    // =========================================================
    // DRAG LEAVE
    // =========================================================

    dropZone.addEventListener('dragleave', function (event) {

        event.preventDefault();

        event.stopPropagation();

        this.style.borderColor = '#adb5bd';

        this.style.backgroundColor = '#f8f9fa';

    });


    // =========================================================
    // DROP
    // =========================================================

    dropZone.addEventListener('drop', function (event) {

        event.preventDefault();

        event.stopPropagation();


        this.style.borderColor = '#adb5bd';

        this.style.backgroundColor = '#f8f9fa';


        if (
            event.dataTransfer &&
            event.dataTransfer.files
        ) {

            addFiles(event.dataTransfer.files);

        }

    });


    // =========================================================
    // CTRL + V
    // =========================================================

    document.addEventListener('paste', function (event) {

        if (!event.clipboardData) {
            return;
        }


        const items =
            Array.from(event.clipboardData.items);


        items.forEach(function (item) {

            if (item.kind !== 'file') {
                return;
            }


            const file = item.getAsFile();


            if (file) {

                addFile(file);

            }

        });


        syncInput();

        renderFiles();

    });


    // =========================================================
    // REMOVE FILE
    // =========================================================

    selectedContainer.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest('.removeDesignFile');


            if (!button) {
                return;
            }


            const index =
                parseInt(
                    button.dataset.index,
                    10
                );


            if (Number.isNaN(index)) {
                return;
            }


            selectedFiles.splice(index, 1);


            syncInput();

            renderFiles();

        }
    );


    // =========================================================
    // PREVENT BROWSER DEFAULT DROP
    // =========================================================

    document.addEventListener(
        'dragover',
        function (event) {

            event.preventDefault();

        }
    );


    document.addEventListener(
        'drop',
        function (event) {

            event.preventDefault();

        }
    );


    // Initial UI
    renderFiles();

});

</script>

@endsection
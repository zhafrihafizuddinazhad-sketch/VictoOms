@extends('layouts.admin')

@section('content')

<div class="content-header">

    <h1 class="mb-4">

        <i class="fas fa-tachometer-alt mr-2"></i>

        Admin Workspace

    </h1>

</div>


{{-- ================================================= --}}
{{-- KPI --}}
{{-- ================================================= --}}

@include('admin.partials.stats', [

    'stats' => $stats,

])


{{-- ================================================= --}}
{{-- NEEDS ATTENTION --}}
{{-- ================================================= --}}

@include('admin.partials.needs-attention', [

    'attentionOrders' => $attentionOrders,

])


{{-- ================================================= --}}
{{-- QUICK ACTIONS --}}
{{-- ================================================= --}}

@include('admin.partials.quick-actions')


{{-- ================================================= --}}
{{-- RECENT ORDERS --}}
{{-- ================================================= --}}

@include('admin.partials.recent-orders', [

    'recentOrders' => $recentOrders,

])


{{-- ================================================= --}}
{{-- DESIGNER WORKLOAD --}}
{{-- ================================================= --}}

@include('admin.partials.designer-workload', [

    'designers' => $designers,

])


@endsection
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}">

    <title>Victo OMS</title>

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

@php
    $isDashboard = request()->routeIs('owner.dashboard', 'admin.dashboard', 'designer.dashboard', 'cameraman.dashboard', 'developer.dashboard');
@endphp

<body class="hold-transition sidebar-mini layout-fixed authenticated-app {{ $isDashboard ? 'dashboard-page' : '' }}" style="--authenticated-background-image: url('{{ asset('images/authenticated-background.jpeg') }}')">

<div class="wrapper">

    {{-- Navbar --}}
    @include('components.navbar')

    @include('components.impersonation-banner')

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Content --}}
    <div class="content-wrapper">

        <section class="content pt-3">
            <div class="container-fluid">

            <div class="page-masthead">
                <div class="page-masthead__brand">
                    <img src="{{ asset('images/victo-logo.png') }}?v=20260917" alt="Victo OMS logo" class="page-masthead__logo">
                    <div>
                        <p class="page-masthead__eyebrow">Creative commerce operations</p>
                        <p class="page-masthead__name">Operations <em>workspace</em></p>
                    </div>
                </div>
                <div class="page-masthead__context">
                    <span class="page-masthead__pulse"></span>
                    Operations workspace
                </div>
            </div>

            @if(session('success'))

    <div class="victo-toast victo-toast--success alert alert-success alert-dismissible fade show" role="status" aria-live="polite">

        <i class="fas fa-check-circle mr-1"></i>

        {{ session('success') }}

        <button
            type="button"
            class="close"
            data-dismiss="alert"
            aria-label="Close">

            <span aria-hidden="true">&times;</span>

        </button>

    </div>

@endif


@if(session('error'))

    <div class="victo-toast victo-toast--error alert alert-danger alert-dismissible fade show" role="alert" aria-live="assertive">

        <i class="fas fa-exclamation-triangle mr-1"></i>

        {{ session('error') }}

        <button
            type="button"
            class="close"
            data-dismiss="alert"
            aria-label="Close">

            <span aria-hidden="true">&times;</span>

        </button>

    </div>

@endif
                @yield('content')

            </div>
        </section>

    </div>

    {{-- Footer --}}
    @include('components.footer')

</div>

<script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>

@stack('scripts')

</body>
</html>

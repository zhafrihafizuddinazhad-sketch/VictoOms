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

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
</head>

<body class="hold-transition sidebar-mini">

<div class="wrapper">

    {{-- Navbar --}}
    @include('components.navbar')

    {{-- Sidebar --}}
    @include('components.sidebar')

    {{-- Content --}}
    <div class="content-wrapper">

        <section class="content pt-3">
            <div class="container-fluid">

            @if(session('success'))

    <div class="alert alert-success alert-dismissible fade show">

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

    <div class="alert alert-danger alert-dismissible fade show">

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
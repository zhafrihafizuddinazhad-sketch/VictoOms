<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Sample Photo Update' }} · Victo</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/fontawesome-free/css/all.min.css') }}">
</head>
<body class="font-sans" style="min-height:100vh;background:#f3f6fc">
    <main class="container py-4 py-md-5" style="max-width:680px">
        <header class="text-center mb-4">
            <img src="{{ asset('images/victo-logo.png') }}?v=20260917" alt="Victo" style="height:56px;max-width:200px;object-fit:contain">
            <div class="small text-muted mt-2">{{ $subtitle ?? 'Sample Photo Update' }}</div>
        </header>
        @yield('content')
        <footer class="text-center text-muted small mt-4">Victo OMS · Contact Victo if you need help with your request.</footer>
    </main>
</body>
</html>

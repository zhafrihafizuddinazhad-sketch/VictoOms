<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased {{ request()->routeIs('login') ? 'login-page-body' : '' }}">
        <div class="guest-shell flex flex-col sm:justify-center items-center pt-6 sm:pt-0 px-4 {{ request()->routeIs('login') ? 'login-shell' : '' }}" @if(request()->routeIs('login')) style="--login-background-image: url('{{ asset('images/login-background.jpeg') }}')" @endif>
            @if(request()->routeIs('login'))
                <section class="login-intro" aria-labelledby="login-intro-title">
                    <div class="login-brand">
                        <a href="/" aria-label="VictoOMS home">
                            <img src="{{ asset('images/victo-logo.png') }}?v=20260917" alt="Victo OMS" class="victo-logo" />
                        </a>
                    </div>
                    <p class="login-intro__eyebrow">VictoOMS</p>
                    <h1 id="login-intro-title">Smarter orders.<br><span>Better workflow.</span></h1>
                    <p class="login-intro__copy">Manage your orders, coordinate your team, and keep everything organised.</p>

                    <div class="login-features" aria-label="VictoOMS features">
                        <span><i aria-hidden="true">✓</i> Orders</span>
                        <span><i aria-hidden="true">✓</i> Design</span>
                        <span><i aria-hidden="true">✓</i> Team</span>
                        <span><i aria-hidden="true">✓</i> Tracking</span>
                    </div>

                    <div class="login-workflow" aria-label="Example order workflow">
                        @foreach(['Order', 'Assigned', 'Design', 'Complete'] as $step)
                            <div class="login-workflow__step"><span class="login-workflow__node" aria-hidden="true"></span><span>{{ $step }}</span></div>
                        @endforeach
                    </div>
                </section>
            @else
                <div class="flex items-center gap-3">
                    <a href="/">
                        <img src="{{ asset('images/victo-logo.png') }}?v=20260917" alt="Victo OMS" class="victo-logo w-16 h-16" />
                    </a>
                    <span class="text-xl font-bold tracking-tight text-slate-800">Victo <span class="text-indigo-600">OMS</span></span>
                </div>
            @endif

            <div class="guest-card w-full sm:max-w-md mt-6 px-7 py-7 bg-white overflow-hidden {{ request()->routeIs('login') ? 'login-card' : '' }}">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>

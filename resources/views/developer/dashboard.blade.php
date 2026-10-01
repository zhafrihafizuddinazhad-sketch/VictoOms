@extends('layouts.admin')

@section('content')
<style>
    .dev-hero { background:#102a43; color:#fff; border-radius:12px; padding:28px; }
    .dev-stat, .dev-role { border:1px solid #e2e8f0; border-radius:10px; background:#fff; height:100%; }
    .dev-stat { padding:18px; }
    .dev-stat i { color:#2563eb; font-size:1.1rem; }
    .dev-stat strong { display:block; color:#102a43; font-size:1.65rem; margin-top:8px; }
    .dev-role { padding:20px; }
    .dev-role h3 { color:#102a43; font-size:1.1rem; }
    .dev-user { border-top:1px solid #edf2f7; padding:12px 0; }
    .dev-user:last-child { padding-bottom:0; }
    .dev-user small { display:block; color:#64748b; }
    .dev-role .btn:focus { outline:3px solid #93c5fd; outline-offset:2px; }
</style>

<section aria-labelledby="developer-title">
    <div class="dev-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <p class="text-uppercase mb-1" style="letter-spacing:.12em;color:#bfdbfe">Developer Console</p>
                <h1 id="developer-title" class="h2 mb-2">Welcome, {{ auth()->user()->name }}</h1>
                <p class="mb-0">You are currently logged in as Developer. Manage and test VictoOMS across different staff roles.</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-3 mt-md-0">
                @csrf
                <button class="btn btn-outline-light" type="submit">Log out</button>
            </form>
        </div>
    </div>

    <h2 class="h4 mb-3">System Overview</h2>
    <div class="row">
        @foreach([
            ['Total Users', 'users', 'fa-users'], ['Active Users', 'activeUsers', 'fa-user-check'],
            ['Inactive / Archived', 'inactiveUsers', 'fa-user-slash'], ['Roles', 'roles', 'fa-id-badge'],
            ['Orders', 'orders', 'fa-shopping-cart'], ['Customers', 'customers', 'fa-address-book'],
            ['Products', 'products', 'fa-box'], ['Job Orders', 'jobOrders', 'fa-clipboard-list'],
        ] as [$label, $key, $icon])
            <div class="col-6 col-md-3 mb-3">
                <div class="dev-stat"><i class="fas {{ $icon }}" aria-hidden="true"></i><strong>{{ number_format($stats[$key]) }}</strong><span>{{ $label }}</span></div>
            </div>
        @endforeach
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end mt-3 mb-3">
        <div><h2 class="h4 mb-1">Manage System As</h2><p class="text-muted mb-0">Choose an active staff account. Your developer account remains unchanged.</p></div>
        <span class="text-muted small mt-2">{{ $usersByRole->flatten()->count() }} active accounts available</span>
    </div>

    <div class="row">
        @foreach([
            'owner' => ['Owner', 'Access VictoOMS as the system owner.', 'fa-crown'],
            'admin' => ['Admin', 'Access VictoOMS as an administrator.', 'fa-user-shield'],
            'designer' => ['Designer', 'Access VictoOMS as a designer.', 'fa-pen-ruler'],
            'cameraman' => ['Cameraman', 'Access VictoOMS as a cameraman.', 'fa-camera'],
        ] as $role => [$title, $description, $icon])
            <div class="col-12 col-md-6 mb-3">
                <article class="dev-role">
                    <div class="d-flex align-items-center mb-2"><i class="fas {{ $icon }} text-primary mr-2" aria-hidden="true"></i><h3 class="mb-0">{{ $title }}</h3></div>
                    <p class="text-muted mb-3">{{ $description }}</p>
                    @forelse($usersByRole[$role] as $user)
                        <div class="dev-user d-flex flex-wrap justify-content-between align-items-center">
                            <div><strong>{{ $user->name }}</strong><small>{{ $user->email }} · {{ ucfirst($user->account_status) }}</small></div>
                            <form method="POST" action="{{ route('developer.impersonate', $user) }}" class="mt-2 mt-sm-0" onsubmit="return confirm('Login as {{ addslashes($title) }}? Your current Developer session will be temporarily switched to this user. You can return to Developer mode at any time.');">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role }}">
                                <button type="submit" class="btn btn-primary btn-sm">Login As {{ $title }}</button>
                            </form>
                        </div>
                    @empty
                        <div class="dev-user text-muted">No active {{ strtolower($title) }} accounts are available.</div>
                    @endforelse
                </article>
            </div>
        @endforeach
    </div>
</section>
@endsection

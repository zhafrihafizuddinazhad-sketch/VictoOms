@extends('layouts.admin')

@section('content')
<section aria-labelledby="developer-title">
    <div class="dev-hero mb-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center">
            <div>
                <p class="dev-hero__eyebrow mb-1">Developer Console</p>
                <h1 id="developer-title" class="h2 mb-2">Developer Console</h1>
                <p class="mb-0">Manage and test VictoOMS across different staff roles.</p>
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
                <div class="dev-stat"><span class="dev-stat__icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></span><strong data-count-up="{{ $stats[$key] }}">{{ number_format($stats[$key]) }}</strong><span>{{ $label }}</span></div>
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
                    <div class="dev-role__heading"><span class="dev-role__icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></span><div><p class="dev-role__eyebrow mb-1">Staff workspace</p><h3 class="mb-0">{{ $title }}</h3></div></div>
                    <p class="text-muted mb-3">{{ $description }}</p>
                    <div class="dev-role__users">{{ $usersByRole[$role]->count() }} active {{ Str::plural('account', $usersByRole[$role]->count()) }}</div>
                    @forelse($usersByRole[$role] as $user)
                        <div class="dev-user d-flex flex-wrap justify-content-between align-items-center">
                            <div><strong>{{ $user->name }}</strong><small>{{ $user->email }} · {{ ucfirst($user->account_status) }}</small></div>
                            <form method="POST" action="{{ route('developer.impersonate', $user) }}" class="mt-2 mt-sm-0" data-confirm="Login as {{ addslashes($title) }}? Your current Developer session will be temporarily switched to this user. You can return to Developer mode at any time.">
                                @csrf
                                <input type="hidden" name="role" value="{{ $role }}">
                                <button type="submit" class="btn btn-primary btn-sm"><span>Login As {{ $title }}</span><i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></button>
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

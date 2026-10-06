@extends('layouts.admin')

@section('title', 'Developer Console')

@section('content')
<section aria-labelledby="developer-title" class="developer-console">
    <div class="dev-hero mb-4">
        <div>
            <p class="dev-hero__eyebrow mb-1">System management</p>
            <h1 id="developer-title" class="h2 mb-2">Developer Console</h1>
            <p class="mb-0">You are currently logged in as Developer. Monitor VictoOMS and manage system access.</p>
        </div>
        <a href="{{ route('accounts.index') }}" class="btn btn-outline-light mt-3 mt-md-0"><i class="fas fa-users-cog mr-1" aria-hidden="true"></i> Manage Accounts</a>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
        <div><h2 class="h4 mb-1">System Overview</h2><p class="text-muted mb-0">Account, role, and operational totals.</p></div>
    </div>
    <div class="row">
        @foreach([
            ['Total Users', 'users', 'fa-users'], ['Active Users', 'activeUsers', 'fa-user-check'],
            ['Inactive Users', 'inactiveUsers', 'fa-user-clock'], ['Archived Users', 'archivedUsers', 'fa-archive'],
            ['Total Orders', 'orders', 'fa-shopping-cart'], ['Orders Today', 'ordersToday', 'fa-calendar-day'],
            ['Active Orders', 'activeOrders', 'fa-spinner'],
        ] as [$label, $key, $icon])
            <div class="col-6 col-lg-3 mb-3"><div class="dev-stat"><span class="dev-stat__icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></span><strong data-count-up="{{ $stats[$key] }}">{{ number_format($stats[$key]) }}</strong><span>{{ $label }}</span></div></div>
        @endforeach
    </div>

    <section class="mb-4" aria-labelledby="role-counts-title">
        <h2 id="role-counts-title" class="h4 mb-3">Role Distribution</h2>
        <div class="row">
            @foreach(['owner' => 'Owner', 'admin' => 'Admin', 'designer' => 'Designer', 'cameraman' => 'Cameraman', 'developer' => 'Developer'] as $role => $label)
                <div class="col-6 col-md mb-3"><div class="dev-stat dev-role-count"><span>{{ $label }}</span><strong>{{ number_format($stats['roleCounts'][$role]) }}</strong></div></div>
            @endforeach
        </div>
    </section>

    <section class="mb-4" aria-labelledby="health-title">
        <div class="d-flex flex-wrap justify-content-between align-items-end mb-3">
            <div><h2 id="health-title" class="h4 mb-1">System Health</h2><p class="text-muted mb-0">Live, safe checks for application dependencies.</p></div>
            <a href="{{ route('developer.health') }}" class="small">View health details <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
        </div>
        <div class="row">
            @foreach($health as $item)
                <div class="col-6 col-md-3 mb-3"><article class="dev-health-card"><span class="dev-health-indicator {{ $item['ok'] ? 'is-ok' : 'is-error' }}" aria-hidden="true"></span><div><strong>{{ $item['label'] }}</strong><span>{{ $item['detail'] }}</span></div></article></div>
            @endforeach
        </div>
    </section>

    <div class="row">
        <div class="col-12 col-xl-7 mb-4">
            <section class="card h-100" aria-labelledby="impersonation-title">
                <div class="card-header"><h2 id="impersonation-title" class="card-title h5 mb-0">Login As</h2></div>
                <div class="card-body">
                    <p class="text-muted">Choose an active Owner, Admin, Designer, or Cameraman account. Your Developer identity and role remain unchanged.</p>
                    <div class="row">
                        @foreach([
                            'owner' => ['Owner', 'fa-crown'], 'admin' => ['Admin', 'fa-user-shield'],
                            'designer' => ['Designer', 'fa-pen-ruler'], 'cameraman' => ['Cameraman', 'fa-camera'],
                        ] as $role => [$title, $icon])
                            <div class="col-12 col-md-6 mb-3">
                                <article class="dev-role">
                                    <div class="dev-role__heading"><span class="dev-role__icon"><i class="fas {{ $icon }}" aria-hidden="true"></i></span><div><p class="dev-role__eyebrow mb-1">{{ $usersByRole[$role]->count() }} active accounts</p><h3 class="mb-0">{{ $title }}</h3></div></div>
                                    @forelse($usersByRole[$role] as $user)
                                        <div class="dev-user d-flex flex-wrap justify-content-between align-items-center">
                                            <div><strong>{{ $user->name }}</strong><small>{{ $user->email }}</small></div>
                                            <form method="POST" action="{{ route('developer.impersonate', $user) }}" class="mt-2 mt-sm-0" data-confirm="Login as {{ $title }}? Your Developer session will be temporarily switched and can be restored at any time.">
                                                @csrf<input type="hidden" name="role" value="{{ $role }}">
                                                <button type="submit" class="btn btn-primary btn-sm"><span>Login As</span><i class="fas fa-arrow-right ml-2" aria-hidden="true"></i></button>
                                            </form>
                                        </div>
                                    @empty
                                        <p class="dev-user text-muted mb-0">No active {{ strtolower($title) }} accounts.</p>
                                    @endforelse
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        </div>
        <div class="col-12 col-xl-5 mb-4">
            <section class="card h-100" aria-labelledby="activity-title">
                <div class="card-header d-flex justify-content-between align-items-center"><h2 id="activity-title" class="card-title h5 mb-0">Recent System Activity</h2><a href="{{ route('developer.activity') }}" class="small">View all</a></div>
                <div class="card-body p-0">
                    @forelse($recentActivity as $entry)
                        <div class="dev-activity"><strong>{{ Str::headline($entry->action) }}</strong><span>{{ $entry->description }}</span><small>{{ $entry->user?->name ?? 'System' }} · {{ $entry->created_at->diffForHumans() }}</small></div>
                    @empty
                        <p class="text-muted p-3 mb-0">No system activity has been recorded yet.</p>
                    @endforelse
                </div>
            </section>
        </div>
    </div>

    <section class="card" aria-labelledby="maintenance-title">
        <div class="card-body d-flex flex-wrap justify-content-between align-items-center">
            <div><h2 id="maintenance-title" class="h5 mb-1">Maintenance Mode</h2><p class="text-muted mb-0">Current status: <strong>{{ $maintenanceEnabled ? 'Enabled' : 'Disabled' }}</strong></p></div>
            <a href="{{ route('developer.maintenance') }}" class="btn btn-outline-primary mt-3 mt-md-0">Manage Maintenance</a>
        </div>
    </section>
</section>
@endsection

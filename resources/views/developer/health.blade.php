@extends('layouts.admin')

@section('title', 'System Health')

@section('content')
<section class="container-fluid" aria-labelledby="system-health-title">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div><h1 id="system-health-title" class="h3 mb-1">System Health</h1><p class="text-muted mb-0">Current application and dependency checks. No credentials or connection details are shown.</p></div>
        <a href="{{ route('developer.dashboard') }}" class="btn btn-outline-secondary mt-2 mt-md-0">Developer Console</a>
    </div>
    <div class="row">
        @foreach($health as $item)
            <div class="col-12 col-sm-6 col-lg-3 mb-3"><article class="dev-health-card dev-health-card--large"><span class="dev-health-indicator {{ $item['ok'] ? 'is-ok' : 'is-error' }}" aria-hidden="true"></span><div><strong>{{ $item['label'] }}</strong><span>{{ $item['detail'] }}</span></div><span class="sr-only">{{ $item['ok'] ? 'Healthy' : 'Unavailable' }}</span></article></div>
        @endforeach
    </div>
    <p class="text-muted small">Queue worker health is not shown because this project has no reliable worker heartbeat check.</p>
</section>
@endsection

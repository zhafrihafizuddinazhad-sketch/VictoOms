@extends('layouts.admin')

@section('title', 'Maintenance Mode')

@section('content')
<section class="container-fluid" aria-labelledby="maintenance-mode-title">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div><h1 id="maintenance-mode-title" class="h3 mb-1">Maintenance Mode</h1><p class="text-muted mb-0">Use Laravel maintenance mode for deployments and urgent maintenance.</p></div>
        <a href="{{ route('developer.dashboard') }}" class="btn btn-outline-secondary mt-2 mt-md-0">Developer Console</a>
    </div>
    <div class="card card-outline {{ $maintenanceEnabled ? 'card-warning' : 'card-success' }}">
        <div class="card-body">
            <p class="h5">Current status: <strong>{{ $maintenanceEnabled ? 'Enabled' : 'Disabled' }}</strong></p>
            @if($maintenanceEnabled)
                <p class="text-muted">Normal visitors receive the maintenance page. This browser has Laravel’s signed bypass so you can disable maintenance.</p>
                <form method="POST" action="{{ route('developer.maintenance.toggle') }}">
                    @csrf
                    <button type="submit" class="btn btn-success">Disable Maintenance Mode</button>
                </form>
            @else
                <p class="text-muted">Enabling this affects all users. The Developer session will receive a temporary signed bypass to continue system administration.</p>
                <form method="POST" action="{{ route('developer.maintenance.toggle') }}" data-confirm="Enable maintenance mode for all users? Normal visitors will see a maintenance page until you disable it.">
                    @csrf
                    <button type="submit" class="btn btn-warning"><i class="fas fa-tools mr-1" aria-hidden="true"></i> Enable Maintenance Mode</button>
                </form>
            @endif
        </div>
    </div>
</section>
@endsection

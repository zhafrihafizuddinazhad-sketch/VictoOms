@extends('layouts.admin')

@section('title', 'System Activity')

@section('content')
<section class="container-fluid" aria-labelledby="system-activity-title">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div><h1 id="system-activity-title" class="h3 mb-1">System Activity</h1><p class="text-muted mb-0">Account lifecycle, role, maintenance, and impersonation actions.</p></div>
        <a href="{{ route('developer.dashboard') }}" class="btn btn-outline-secondary mt-2 mt-md-0">Developer Console</a>
    </div>
    <div class="card"><div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead><tr><th>Action</th><th>Description</th><th>Performed By</th><th>Time</th></tr></thead>
            <tbody>
                @forelse($activities as $activity)
                    <tr><td>{{ Str::headline($activity->action) }}</td><td>{{ $activity->description }}</td><td>{{ $activity->user?->name ?? 'System' }}</td><td>{{ $activity->created_at?->format('d M Y, H:i') }}</td></tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted py-4">No system activity recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div><div class="card-footer">{{ $activities->links() }}</div></div>
</section>
@endsection

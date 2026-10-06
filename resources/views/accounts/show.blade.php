@extends('layouts.admin')

@section('title', 'Account Details')

@section('content')
<div class="container-fluid accounts-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div><h1 class="h3 mb-1">Account Details</h1><p class="text-muted mb-0">Staff account information and assigned role.</p></div>
        <a href="{{ route('accounts.index') }}" class="btn btn-outline-secondary mt-2 mt-md-0"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i> Back to Accounts</a>
    </div>
    <div class="card card-primary card-outline">
        <div class="card-body">
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt><dd class="col-sm-9">{{ $staff->name }}</dd>
                <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $staff->email }}</dd>
                <dt class="col-sm-3">Phone</dt><dd class="col-sm-9">{{ $staff->phone ?: '—' }}</dd>
                <dt class="col-sm-3">Role</dt><dd class="col-sm-9">{{ $staff->getRoleNames()->map(fn ($role) => ucfirst($role))->join(', ') ?: 'Unassigned' }}</dd>
                <dt class="col-sm-3">Status</dt><dd class="col-sm-9">{{ ucfirst($staff->account_status) }}</dd>
                <dt class="col-sm-3">Member Since</dt><dd class="col-sm-9">{{ $staff->created_at?->format('d M Y, H:i') ?? '—' }}</dd>
                <dt class="col-sm-3">Last Updated</dt><dd class="col-sm-9">{{ $staff->updated_at?->format('d M Y, H:i') ?? '—' }}</dd>
            </dl>
        </div>
    </div>
</div>
@endsection

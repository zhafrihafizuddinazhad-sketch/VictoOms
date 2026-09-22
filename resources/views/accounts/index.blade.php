@extends('layouts.admin')

@section('title', 'Account Management')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="m-0">
                <i class="fas fa-user-cog"></i>
                Account Management
            </h1>

            <p class="text-muted mb-0">
                Manage VictoOMS staff accounts.
            </p>
        </div>

        <a href="{{ route('accounts.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus"></i>
            Add Account
        </a>
    </div>


    {{-- Success Message --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle"></i>
            {{ session('success') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert"
                    aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif


    {{-- Error Message --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}

            <button type="button"
                    class="close"
                    data-dismiss="alert"
                    aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif


    {{-- Status Filter --}}
    <div class="mb-3">
        <div class="btn-group" role="group">

            <a href="{{ route('accounts.index', ['status' => 'active']) }}"
               class="btn {{ $status === 'active' ? 'btn-primary' : 'btn-outline-primary' }}">
                <i class="fas fa-user-check"></i>
                Active
            </a>

            <a href="{{ route('accounts.index', ['status' => 'inactive']) }}"
               class="btn {{ $status === 'inactive' ? 'btn-secondary' : 'btn-outline-secondary' }}">
                <i class="fas fa-user-clock"></i>
                Inactive
            </a>

            <a href="{{ route('accounts.index', ['status' => 'archived']) }}"
               class="btn {{ $status === 'archived' ? 'btn-dark' : 'btn-outline-dark' }}">
                <i class="fas fa-archive"></i>
                Archived
            </a>

        </div>
    </div>


    {{-- Account List --}}
    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-users"></i>
                Staff Accounts
            </h3>
        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Member Since</th>
                            <th style="width: 220px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($users as $index => $staff)

                            <tr>

                                {{-- Number --}}
                                <td>
                                    {{ $index + 1 }}
                                </td>


                                {{-- Name --}}
                                <td>
                                    <div class="d-flex align-items-center">

                                        <div
                                            class="bg-primary rounded-circle d-flex align-items-center justify-content-center mr-2"
                                            style="width: 38px; height: 38px;"
                                        >
                                            <i class="fas fa-user text-white"></i>
                                        </div>

                                        <strong>
                                            {{ $staff->name }}
                                        </strong>

                                    </div>
                                </td>


                                {{-- Email --}}
                                <td>
                                    {{ $staff->email }}
                                </td>


                                {{-- Phone --}}
                                <td>
                                    {{ $staff->phone ?? '-' }}
                                </td>


                                {{-- Role --}}
                                <td>

                                    @foreach($staff->getRoleNames() as $role)

                                        @php
                                            $badgeClass = match($role) {
                                                'owner' => 'badge-dark',
                                                'admin' => 'badge-primary',
                                                'designer' => 'badge-info',
                                                'cameraman' => 'badge-success',
                                                default => 'badge-secondary',
                                            };
                                        @endphp

                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst($role) }}
                                        </span>

                                    @endforeach

                                </td>


                                {{-- Status --}}
                                <td>

                                    @if($staff->account_status === 'active')

                                        <span class="badge badge-success">
                                            <i class="fas fa-check-circle"></i>
                                            Active
                                        </span>

                                    @elseif($staff->account_status === 'inactive')

                                        <span class="badge badge-warning">
                                            <i class="fas fa-pause-circle"></i>
                                            Inactive
                                        </span>

                                    @elseif($staff->account_status === 'archived')

                                        <span class="badge badge-dark">
                                            <i class="fas fa-archive"></i>
                                            Archived
                                        </span>

                                    @else

                                        <span class="badge badge-secondary">
                                            Unknown
                                        </span>

                                    @endif

                                </td>


                                {{-- Member Since --}}
                                <td>
                                    {{ $staff->created_at?->format('d M Y') ?? '-' }}
                                </td>


                                {{-- Actions --}}
                                <td>

                                    @if($staff->id === auth()->id())

                                        <span class="text-muted">
                                            <i class="fas fa-user"></i>
                                            Current Account
                                        </span>

                                    @elseif($staff->account_status === 'active')

                                        <form action="{{ route('accounts.deactivate', $staff) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Deactivate this account? The staff member will no longer be able to login or receive new assignments.');">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-sm btn-warning">
                                                <i class="fas fa-user-slash"></i>
                                                Deactivate
                                            </button>

                                        </form>

                                    @elseif($staff->account_status === 'inactive')

                                        <form action="{{ route('accounts.reactivate', $staff) }}"
                                              method="POST"
                                              class="d-inline">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-sm btn-success">
                                                <i class="fas fa-user-check"></i>
                                                Reactivate
                                            </button>

                                        </form>

                                        <form action="{{ route('accounts.archive', $staff) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Archive this account? It will no longer appear in the Active or Inactive account lists.');">

                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-sm btn-dark">
                                                <i class="fas fa-archive"></i>
                                                Archive
                                            </button>

                                        </form>

                                    @elseif($staff->account_status === 'archived')

    <form action="{{ route('accounts.restore', $staff) }}"
          method="POST"
          class="d-inline"
          onsubmit="return confirm('Restore this account? The account will be moved back to Inactive status.');">

        @csrf
        @method('PATCH')

        <button type="submit"
                class="btn btn-sm btn-secondary">
            <i class="fas fa-undo"></i>
            Restore
        </button>

    </form>

                                    @endif

                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="8" class="text-center py-4">

                                    <i class="fas fa-users fa-2x text-muted mb-2"></i>

                                    <p class="text-muted mb-0">
                                        No staff accounts found.
                                    </p>

                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

@endsection
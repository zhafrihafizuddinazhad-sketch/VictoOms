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
                            <th>Member Since</th>
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


                                {{-- Member Since --}}
                                <td>
                                    {{ $staff->created_at?->format('d M Y') ?? '-' }}
                                </td>

                            </tr>

                        @empty

                            <tr>
                                <td colspan="6" class="text-center py-4">

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
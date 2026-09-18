@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    {{-- ========================================================= --}}
    {{-- PAGE HEADER --}}
    {{-- ========================================================= --}}

    <div class="mb-4">

        <h1 class="mb-1">
            <i class="fas fa-user-circle mr-2"></i>
            My Profile
        </h1>

        <p class="text-muted mb-0">
            Manage your profile information and account password.
        </p>

    </div>


    {{-- ========================================================= --}}
    {{-- SUCCESS MESSAGE --}}
    {{-- ========================================================= --}}

    @if(session('success'))

        <div class="alert alert-success alert-dismissible fade show">

            <i class="fas fa-check-circle mr-1"></i>

            {{ session('success') }}

            <button
                type="button"
                class="close"
                data-dismiss="alert"
            >
                <span>&times;</span>
            </button>

        </div>

    @endif


    {{-- ========================================================= --}}
    {{-- VALIDATION ERRORS --}}
    {{-- ========================================================= --}}

    @if($errors->any())

        <div class="alert alert-danger">

            <strong>
                Please check the following:
            </strong>

            <ul class="mb-0 mt-2">

                @foreach($errors->all() as $error)

                    <li>
                        {{ $error }}
                    </li>

                @endforeach

            </ul>

        </div>

    @endif


    <div class="row">


        {{-- ===================================================== --}}
        {{-- PROFILE CARD --}}
        {{-- ===================================================== --}}

        <div class="col-lg-4">

            <div class="card card-primary card-outline">

                <div class="card-body box-profile">

                    {{-- Profile Picture --}}
                    <div class="text-center mb-3">

                        @if($user->profile_picture)

                            <img
                                class="profile-user-img img-fluid img-circle"
                                src="{{ asset('storage/' . $user->profile_picture) }}"
                                alt="Profile Picture"
                                style="
                                    width: 120px;
                                    height: 120px;
                                    object-fit: cover;
                                "
                            >

                        @else

                            <div
                                class="profile-user-img img-fluid img-circle d-flex align-items-center justify-content-center mx-auto"
                                style="
                                    width: 120px;
                                    height: 120px;
                                    background: #e9ecef;
                                    font-size: 50px;
                                    color: #6c757d;
                                "
                            >

                                <i class="fas fa-user"></i>

                            </div>

                        @endif

                    </div>


                    {{-- Name --}}
                    <h3 class="profile-username text-center">

                        {{ $user->name }}

                    </h3>


                    {{-- Role --}}
                    <p class="text-muted text-center">

                        @if($user->roles->count())

                            {{ $user->getRoleNames()->implode(', ') }}

                        @else

                            User

                        @endif

                    </p>


                    <ul class="list-group list-group-unbordered mb-3">

                        {{-- Email --}}
                        <li class="list-group-item">

                            <b>
                                <i class="fas fa-envelope mr-1"></i>
                                Email
                            </b>

                            <span class="float-right text-muted">

                                {{ $user->email }}

                            </span>

                        </li>


                        {{-- Phone --}}
                        <li class="list-group-item">

                            <b>
                                <i class="fas fa-phone mr-1"></i>
                                Phone
                            </b>

                            <span class="float-right text-muted">

                                {{ $user->phone ?? 'Not provided' }}

                            </span>

                        </li>


                        {{-- Member Since --}}
                        <li class="list-group-item">

                            <b>
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Member Since
                            </b>

                            <span class="float-right text-muted">

                                {{ $user->created_at
                                    ? $user->created_at->format('d M Y')
                                    : '-' }}

                            </span>

                        </li>

                    </ul>

                </div>

            </div>

        </div>


        {{-- ===================================================== --}}
        {{-- RIGHT SIDE --}}
        {{-- ===================================================== --}}

        <div class="col-lg-8">


            {{-- ================================================= --}}
            {{-- PROFILE INFORMATION --}}
            {{-- ================================================= --}}

            <div class="card card-outline card-primary">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-user-edit mr-1"></i>

                        Profile Information

                    </h3>

                </div>


                <form
                    method="POST"
                    action="{{ route('profile.update') }}"
                >

                    @csrf

                    @method('PATCH')


                    <div class="card-body">


                        {{-- Name --}}
                        <div class="form-group">

                            <label for="name">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                id="name"
                                class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name', $user->name) }}"
                                required
                            >

                            @error('name')

                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        {{-- Email --}}
                        <div class="form-group">

                            <label for="email">
                                Email Address
                            </label>

                            <input
                                type="email"
                                name="email"
                                id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email', $user->email) }}"
                                required
                            >

                            @error('email')

                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        {{-- Phone --}}
                        <div class="form-group">

                            <label for="phone">
                                Phone Number
                            </label>

                            <input
                                type="text"
                                name="phone"
                                id="phone"
                                class="form-control @error('phone') is-invalid @enderror"
                                value="{{ old('phone', $user->phone) }}"
                                placeholder="e.g. 0123456789"
                            >

                            @error('phone')

                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        {{-- Role --}}
                        <div class="form-group">

                            <label>
                                Role
                            </label>

                            <input
                                type="text"
                                class="form-control"
                                value="{{ $user->getRoleNames()->implode(', ') ?: 'User' }}"
                                readonly
                            >

                            <small class="form-text text-muted">
                                Your role can only be changed by an authorized administrator.
                            </small>

                        </div>

                    </div>


                    <div class="card-footer text-right">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fas fa-save mr-1"></i>

                            Save Changes

                        </button>

                    </div>

                </form>

            </div>


            {{-- ================================================= --}}
            {{-- CHANGE PASSWORD --}}
            {{-- ================================================= --}}

            <div class="card card-outline card-warning">

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-lock mr-1"></i>

                        Change Password

                    </h3>

                </div>


                <form
                    method="POST"
                    action="{{ route('profile.password.update') }}"
                >

                    @csrf

                    @method('PUT')


                    <div class="card-body">


                        {{-- Current Password --}}
                        <div class="form-group">

                            <label for="current_password">
                                Current Password
                            </label>

                            <input
                                type="password"
                                name="current_password"
                                id="current_password"
                                class="form-control @error('current_password') is-invalid @enderror"
                                required
                            >

                            @error('current_password')

                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        {{-- New Password --}}
                        <div class="form-group">

                            <label for="password">
                                New Password
                            </label>

                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                required
                            >

                            @error('password')

                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>

                            @enderror

                        </div>


                        {{-- Confirm Password --}}
                        <div class="form-group">

                            <label for="password_confirmation">
                                Confirm New Password
                            </label>

                            <input
                                type="password"
                                name="password_confirmation"
                                id="password_confirmation"
                                class="form-control"
                                required
                            >

                        </div>

                    </div>


                    <div class="card-footer text-right">

                        <button
                            type="submit"
                            class="btn btn-warning"
                        >

                            <i class="fas fa-key mr-1"></i>

                            Update Password

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

@endsection
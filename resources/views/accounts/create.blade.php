@extends('layouts.admin')

@section('title', 'Add Account')

@section('content')

<div class="container-fluid">

    {{-- Page Header --}}
    <div class="mb-3">
        <h1 class="m-0">
            <i class="fas fa-user-plus"></i>
            Add Account
        </h1>

        <p class="text-muted mb-0">
            Create a new VictoOMS staff account.
        </p>
    </div>


    {{-- Validation Errors --}}
    @if($errors->any())
        <div class="alert alert-danger">
            <h6>
                <i class="fas fa-exclamation-triangle"></i>
                Please check the following:
            </h6>

            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif


    <div class="card card-primary card-outline">

        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-circle"></i>
                Account Information
            </h3>
        </div>


        <form
            action="{{ route('accounts.store') }}"
            method="POST"
        >

            @csrf

            <div class="card-body">

                {{-- Name --}}
                <div class="form-group">
                    <label for="name">
                        Name <span class="text-danger">*</span>
                    </label>

                    <input
                        type="text"
                        name="name"
                        id="name"
                        class="form-control @error('name') is-invalid @enderror"
                        value="{{ old('name') }}"
                        placeholder="Enter staff name"
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
                        Email <span class="text-danger">*</span>
                    </label>

                    <input
                        type="email"
                        name="email"
                        id="email"
                        class="form-control @error('email') is-invalid @enderror"
                        value="{{ old('email') }}"
                        placeholder="Enter staff email"
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
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        id="phone"
                        class="form-control @error('phone') is-invalid @enderror"
                        value="{{ old('phone') }}"
                        placeholder="Enter phone number"
                    >

                    @error('phone')
                        <span class="invalid-feedback">
                            {{ $message }}
                        </span>
                    @enderror
                </div>


                {{-- Role --}}
                <div class="form-group">
                    <label for="role">
                        Role <span class="text-danger">*</span>
                    </label>

                    <select
                        name="role"
                        id="role"
                        class="form-control @error('role') is-invalid @enderror"
                        required
                    >
                        <option value="">
                            Select role
                        </option>

                        @foreach($roles as $role)
                            <option
                                value="{{ $role }}"
                                {{ old('role') === $role ? 'selected' : '' }}
                            >
                                {{ ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>

                    @error('role')
                        <span class="invalid-feedback">
                            {{ $message }}
                        </span>
                    @enderror
                </div>


                {{-- Password --}}
                <div class="form-group">
                    <label for="password">
                        Password <span class="text-danger">*</span>
                    </label>

                    <input
                        type="password"
                        name="password"
                        id="password"
                        class="form-control @error('password') is-invalid @enderror"
                        placeholder="Minimum 8 characters"
                        required
                    >

                    @error('password')
                        <span class="invalid-feedback">
                            {{ $message }}
                        </span>
                    @enderror
                </div>


                {{-- Confirm Password --}}
                <div class="form-group mb-0">
                    <label for="password_confirmation">
                        Confirm Password <span class="text-danger">*</span>
                    </label>

                    <input
                        type="password"
                        name="password_confirmation"
                        id="password_confirmation"
                        class="form-control"
                        placeholder="Re-enter password"
                        required
                    >
                </div>

            </div>


            {{-- Footer --}}
            <div class="card-footer d-flex justify-content-end">

                <a
                    href="{{ route('accounts.index') }}"
                    class="btn btn-secondary mr-2"
                >
                    <i class="fas fa-arrow-left"></i>
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    <i class="fas fa-user-plus"></i>
                    Create Account
                </button>

            </div>

        </form>

    </div>

</div>

@endsection
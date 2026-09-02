@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="container-fluid">


        {{-- ================================================= --}}
        {{-- HEADER --}}
        {{-- ================================================= --}}

        <div class="d-flex justify-content-between align-items-center mb-3">

            <div>

                <h1 class="mb-1">

                    <i class="fas fa-bell"></i>

                    Notifications

                </h1>

                <small class="text-muted">

                    Stay updated with your order activities.

                </small>

            </div>


            <div>

                @if($notifications->total() > 0)

                    <form
                        action="{{ route('notifications.readAll') }}"
                        method="POST"
                        class="d-inline">

                        @csrf
                        @method('PATCH')

                        <button
                            type="submit"
                            class="btn btn-outline-primary btn-sm">

                            <i class="fas fa-check-double mr-1"></i>

                            Mark All as Read

                        </button>

                    </form>

                @endif

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- NOTIFICATION CARD --}}
        {{-- ================================================= --}}

        <div class="card shadow-sm">

            <div class="card-header">

                <div class="d-flex justify-content-between align-items-center">

                    <strong>

                        <i class="fas fa-inbox mr-1"></i>

                        Notification Inbox

                    </strong>


                    <span class="badge badge-primary">

                        {{ $notifications->total() }}

                        Notification(s)

                    </span>

                </div>

            </div>


            <div class="card-body p-0">


                @forelse($notifications as $notification)


                    {{-- ================================================= --}}
                    {{-- NOTIFICATION ITEM --}}
                    {{-- ================================================= --}}

                    <div
                        class="notification-item p-3 border-bottom
                        {{ !$notification->is_read ? 'notification-unread' : '' }}">


                        <div class="d-flex justify-content-between align-items-start">


                            {{-- ================================================= --}}
                            {{-- LEFT --}}
                            {{-- ================================================= --}}

                            <div class="pr-3">


                                <h5 class="mb-1">

                                    @if(!$notification->is_read)

                                        <i class="fas fa-circle text-primary"
                                           style="font-size: 8px;">
                                        </i>

                                    @endif


                                    {{ $notification->title }}


                                    @if(!$notification->is_read)

                                        <span class="badge badge-danger ml-1">

                                            New

                                        </span>

                                    @endif

                                </h5>


                                <p class="mb-1 text-muted">

                                    {{ $notification->message }}

                                </p>


                                <small class="text-muted">

                                    <i class="far fa-clock mr-1"></i>

                                    {{ $notification->created_at->diffForHumans() }}

                                </small>

                            </div>


                            {{-- ================================================= --}}
                            {{-- RIGHT ACTION --}}
                            {{-- ================================================= --}}

                            <div class="text-nowrap">


                                @if($notification->order_id)

                                    <form
                                        action="{{ route('notifications.read', $notification) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf
                                        @method('PATCH')


                                        <button
                                            type="submit"
                                            class="btn btn-sm
                                            {{ $notification->is_read
                                                ? 'btn-outline-secondary'
                                                : 'btn-primary' }}">

                                            <i class="fas fa-external-link-alt mr-1"></i>

                                            Open Order

                                        </button>

                                    </form>


                                @elseif(!$notification->is_read)

                                    <form
                                        action="{{ route('notifications.read', $notification) }}"
                                        method="POST"
                                        class="d-inline">

                                        @csrf
                                        @method('PATCH')


                                        <button
                                            type="submit"
                                            class="btn btn-success btn-sm">

                                            <i class="fas fa-check mr-1"></i>

                                            Mark as Read

                                        </button>

                                    </form>


                                @else

                                    <span class="badge badge-success">

                                        <i class="fas fa-check mr-1"></i>

                                        Read

                                    </span>

                                @endif


                            </div>

                        </div>

                    </div>


                @empty


                    {{-- ================================================= --}}
                    {{-- EMPTY --}}
                    {{-- ================================================= --}}

                    <div class="text-center py-5">

                        <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>


                        <h5>

                            No notifications yet.

                        </h5>


                        <p class="text-muted mb-0">

                            You're all caught up.

                        </p>

                    </div>


                @endforelse

            </div>

        </div>


        {{-- ================================================= --}}
        {{-- PAGINATION --}}
        {{-- ================================================= --}}

        @if($notifications->hasPages())

            <div class="mt-3">

                {{ $notifications->links() }}

            </div>

        @endif

    </div>

</div>


{{-- ========================================================= --}}
{{-- CUSTOM CSS --}}
{{-- ========================================================= --}}

<style>

.notification-item {

    transition: background-color 0.2s ease;

}


.notification-item:hover {

    background-color: #f8f9fa;

}


.notification-unread {

    background-color: #f0f7ff;

}


.notification-unread:hover {

    background-color: #e8f2ff;

}

</style>

@endsection
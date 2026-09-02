<nav class="main-header navbar navbar-expand navbar-white navbar-light">

    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-lte-toggle="sidebar" href="#">
                <i class="fas fa-bars"></i>
            </a>
        </li>
    </ul>

   @php

    /*
    |--------------------------------------------------------------------------
    | Latest Notifications
    |--------------------------------------------------------------------------
    */

    $notifications = \App\Models\Notification::where(
            'user_id',
            auth()->id()
        )
        ->latest()
        ->take(5)
        ->get();


    /*
    |--------------------------------------------------------------------------
    | Total Unread Notifications
    |--------------------------------------------------------------------------
    */

    $unreadCount = \App\Models\Notification::where(
            'user_id',
            auth()->id()
        )
        ->where('is_read', false)
        ->count();

@endphp

    <ul class="navbar-nav ml-auto">

        {{-- Notification --}}
        <li class="nav-item dropdown me-3">

            <a class="nav-link"
               data-toggle="dropdown"
               href="#">

                <i class="fas fa-bell fa-lg"></i>

                @if($unreadCount)

                    <span class="badge bg-danger navbar-badge">

                        {{ $unreadCount }}

                    </span>

                @endif

            </a>

            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

                <span class="dropdown-header">

    @if($unreadCount > 0)

        {{ $unreadCount }} Unread Notification(s)

    @else

        No Unread Notifications

    @endif

</span>

                <div class="dropdown-divider"></div>

                @forelse($notifications as $notification)

<a href="#"
   onclick="event.preventDefault();
            document.getElementById('read-{{ $notification->id }}').submit();"
   class="dropdown-item {{ !$notification->is_read ? 'bg-light' : '' }}">

    <div class="d-flex">

        <div class="me-2">

            @switch($notification->title)

                @case('New Order Assigned')

                    <i class="fas fa-user-plus text-primary"></i>

                    @break

                @case('Revision Requested')

                    <i class="fas fa-undo text-warning"></i>

                    @break

                @case('Design Approved')

                    <i class="fas fa-check-circle text-success"></i>

                    @break

                @case('Photo Session Required')

                    <i class="fas fa-camera text-info"></i>

                    @break

                @default

                    <i class="fas fa-bell text-secondary"></i>

            @endswitch

        </div>

        <div class="flex-grow-1">

            <strong>

                {{ $notification->title }}

            </strong>

            @if(!$notification->is_read)

                <span class="badge bg-danger float-end">

                    NEW

                </span>

            @endif

            <br>

            <small>

                {{ $notification->message }}

            </small>

            <br>

            <small class="text-muted">

                <i class="far fa-clock"></i>

                {{ $notification->created_at->diffForHumans() }}

            </small>

        </div>

    </div>

</a>

<form
    id="read-{{ $notification->id }}"
    action="{{ route('notifications.read',$notification) }}"
    method="POST"
    class="d-none">

    @csrf
    @method('PATCH')

</form>

<div class="dropdown-divider"></div>

@empty

<span class="dropdown-item text-center text-muted">

    No Notification

</span>

@endforelse

                @if($unreadCount > 0)

    <div class="dropdown-divider"></div>

    <form
        action="{{ route('notifications.readAll') }}"
        method="POST">

        @csrf
        @method('PATCH')

        <button
            type="submit"
            class="dropdown-item text-center">

            <i class="fas fa-check-double mr-1"></i>

            Mark All as Read

        </button>

    </form>

@endif


<a
    href="{{ route('notifications.index') }}"
    class="dropdown-item dropdown-footer">

    <i class="fas fa-list mr-1"></i>

    View All Notifications

</a>

            </div>

        </li>

        {{-- User --}}
        <li class="nav-item mr-3 d-flex align-items-center">

            <span class="mr-3">
                <i class="fas fa-user-circle"></i>
                {{ auth()->user()->name }}
            </span>

        </li>

        {{-- Logout --}}
        <li class="nav-item">

            <form action="{{ route('logout') }}" method="POST">

                @csrf

                <button
                    type="submit"
                    class="btn btn-danger btn-sm mt-1 mr-2">

                    <i class="fas fa-sign-out-alt"></i>

                    Logout

                </button>

            </form>

        </li>

    </ul>

</nav>
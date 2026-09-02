<div class="card shadow-sm mb-4">

    {{-- ===================================================== --}}
    {{-- HEADER --}}
    {{-- ===================================================== --}}

    <div class="card-header bg-dark text-white">

        <h5 class="mb-0">
            <i class="fas fa-stream"></i>
            Order Timeline
        </h5>

    </div>


    <div class="card-body">

        @php

            /*
            |--------------------------------------------------------------------------
            | Timeline Stage Definitions
            |--------------------------------------------------------------------------
            */

            $timeline = [

                'Pending' => [
                    'label' => 'Order Created',
                    'icon' => 'fa-plus-circle',
                ],

                'Assigned' => [
                    'label' => 'Designer Assigned',
                    'icon' => 'fa-user-edit',
                ],

                'In Progress' => [
                    'label' => 'Designing',
                    'icon' => 'fa-paint-brush',
                ],

                'Pending Approval' => [
                    'label' => 'Owner Review',
                    'icon' => 'fa-search',
                ],

                'Printing' => [
                    'label' => 'Printing',
                    'icon' => 'fa-print',
                ],

                'Ready at HQ' => [
                    'label' => 'Ready at HQ',
                    'icon' => 'fa-building',
                ],

                'Photo Session' => [
                    'label' => 'Photo Session',
                    'icon' => 'fa-camera',
                ],

                'Photo Completed' => [
                    'label' => 'Photo Completed',
                    'icon' => 'fa-check-circle',
                ],

                'Out for Delivery' => [
                    'label' => 'Out for Delivery',
                    'icon' => 'fa-truck',
                ],

                'Waiting for Pickup' => [
                    'label' => 'Waiting for Pickup',
                    'icon' => 'fa-box-open',
                ],

                'Completed' => [
                    'label' => 'Completed',
                    'icon' => 'fa-flag-checkered',
                ],

            ];


            /*
            |--------------------------------------------------------------------------
            | Current Order Status
            |--------------------------------------------------------------------------
            */

            $currentStatus = $order->status;


            /*
            |--------------------------------------------------------------------------
            | Build Timeline Based On Delivery Method
            |--------------------------------------------------------------------------
            */

            if ($order->delivery_method === 'Self Pickup') {

                $statuses = [

                    'Pending',
                    'Assigned',
                    'In Progress',
                    'Pending Approval',
                    'Printing',
                    'Ready at HQ',
                    'Photo Session',
                    'Photo Completed',
                    'Waiting for Pickup',
                    'Completed',

                ];

            } else {

                $statuses = [

                    'Pending',
                    'Assigned',
                    'In Progress',
                    'Pending Approval',
                    'Printing',
                    'Ready at HQ',
                    'Photo Session',
                    'Photo Completed',
                    'Out for Delivery',
                    'Completed',

                ];

            }


            /*
            |--------------------------------------------------------------------------
            | Find Current Stage
            |--------------------------------------------------------------------------
            */

            $currentIndex = array_search(
                $currentStatus,
                $statuses
            );


            /*
            |--------------------------------------------------------------------------
            | Fallback
            |--------------------------------------------------------------------------
            */

            if ($currentIndex === false) {

                $currentIndex = 0;

            }


            /*
            |--------------------------------------------------------------------------
            | Activity Logs
            |--------------------------------------------------------------------------
            */

            $activities = $order->activityLogs
                ->sortByDesc('created_at');

        @endphp


        {{-- ===================================================== --}}
        {{-- ORDER TIMELINE --}}
        {{-- ===================================================== --}}

        <div class="timeline">

            @foreach($statuses as $status)

                @php

                    $step = $timeline[$status];

                    $stepIndex = array_search(
                        $status,
                        $statuses
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Determine Stage State
                    |--------------------------------------------------------------------------
                    */

                    if ($stepIndex < $currentIndex) {

                        $state = 'completed';

                    } elseif ($stepIndex === $currentIndex) {

                        $state = 'current';

                    } else {

                        $state = 'upcoming';

                    }

                @endphp


                {{-- ================================================= --}}
                {{-- TIMELINE ITEM --}}
                {{-- ================================================= --}}

                <div class="timeline-item {{ $state }}">


                    {{-- ICON --}}

                    <div class="timeline-icon">

                        <i class="fas {{ $step['icon'] }}"></i>

                    </div>


                    {{-- CONTENT --}}

                    <div class="timeline-content">

                        <div class="d-flex align-items-center flex-wrap">


                            {{-- STAGE NAME --}}

                            <strong class="timeline-title">

                                {{ $step['label'] }}

                            </strong>


                            {{-- STATUS BADGE --}}

                            @if($state === 'completed')

                                <span class="badge badge-success ml-2">

                                    Completed

                                </span>

                            @elseif($state === 'current')

                                <span class="badge badge-warning ml-2">

                                    Current Stage

                                </span>

                            @else

                                <span class="badge badge-secondary ml-2">

                                    Upcoming

                                </span>

                            @endif

                        </div>


                        {{-- ================================================= --}}
                        {{-- OPTIONAL STAGE DESCRIPTION --}}
                        {{-- ================================================= --}}

                        @if($state === 'current')

                            <div class="timeline-stage-description text-muted mt-1">

                                @if($status === 'Pending')

                                    Waiting for designer assignment.

                                @elseif($status === 'Assigned')

                                    Designer has been assigned to this order.

                                @elseif($status === 'In Progress')

                                    Designer is currently working on the order.

                                @elseif($status === 'Pending Approval')

                                    Design has been submitted and is waiting
                                    for owner approval.

                                @elseif($status === 'Printing')

                                    Order is currently being printed.

                                @elseif($status === 'Ready at HQ')

                                    Order is ready at HQ for the photo session.

                                @elseif($status === 'Photo Session')

                                    Photo session is currently in progress.

                                @elseif($status === 'Photo Completed')

                                    Photo session has been completed.

                                @elseif($status === 'Out for Delivery')

                                    Order is currently out for delivery.

                                @elseif($status === 'Waiting for Pickup')

                                    Order is ready and waiting for customer pickup.

                                @elseif($status === 'Completed')

                                    Order has been completed.

                                @endif

                            </div>

                        @endif

                    </div>

                </div>

            @endforeach

        </div>


        {{-- ===================================================== --}}
        {{-- ACTIVITY HISTORY --}}
        {{-- ===================================================== --}}

        <div class="activity-history">

            <hr class="my-4">


            <h6 class="font-weight-bold mb-3">

                <i class="fas fa-history mr-1"></i>

                Activity History

            </h6>


            @if($activities->count())

                <div class="list-group">

                    @foreach($activities as $activity)

                        <div class="list-group-item">


                            <div class="d-flex justify-content-between align-items-start">


                                {{-- ================================================= --}}
                                {{-- ACTIVITY INFORMATION --}}
                                {{-- ================================================= --}}

                                <div>

                                    <strong>

                                        {{ $activity->action }}

                                    </strong>


                                    @if($activity->description)

                                        <div class="text-muted mt-1">

                                            {{ $activity->description }}

                                        </div>

                                    @endif


                                    @if($activity->user)

                                        <small class="text-muted d-block mt-1">

                                            <i class="fas fa-user mr-1"></i>

                                            By {{ $activity->user->name }}

                                        </small>

                                    @endif

                                </div>


                                {{-- ================================================= --}}
                                {{-- ACTIVITY DATE --}}
                                {{-- ================================================= --}}

                                @if($activity->created_at)

                                    <small class="text-muted ml-3 text-nowrap">

                                        {{ $activity->created_at->format('d M Y, h:i A') }}

                                    </small>

                                @endif

                            </div>

                        </div>

                    @endforeach

                </div>

            @else

                <div class="alert alert-secondary mb-0">

                    <i class="fas fa-info-circle mr-1"></i>

                    No activity recorded yet.

                </div>

            @endif

        </div>

    </div>

</div>


{{-- ========================================================= --}}
{{-- TIMELINE CSS --}}
{{-- ========================================================= --}}

<style>

.timeline {

    position: relative;

    padding-left: 55px;

}


/* Vertical Line */

.timeline::before {

    content: '';

    position: absolute;

    left: 18px;

    top: 10px;

    bottom: 10px;

    width: 3px;

    background: #dee2e6;

}


/* Timeline Item */

.timeline-item {

    position: relative;

    min-height: 70px;

    margin-bottom: 8px;

}


/* Timeline Icon */

.timeline-icon {

    position: absolute;

    left: -55px;

    top: 0;

    width: 40px;

    height: 40px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #adb5bd;

    color: white;

    z-index: 2;

}


/* Timeline Content */

.timeline-content {

    padding: 7px 0 22px 10px;

}


/* Stage Title */

.timeline-title {

    font-size: 17px;

}


/* Completed */

.timeline-item.completed .timeline-icon {

    background: #28a745;

}


.timeline-item.completed .timeline-title {

    color: #212529;

}


/* Current */

.timeline-item.current .timeline-icon {

    background: #ffc107;

    color: #212529;

}


.timeline-item.current .timeline-title {

    color: #d39e00;

}


/* Upcoming */

.timeline-item.upcoming .timeline-icon {

    background: #adb5bd;

}


.timeline-item.upcoming .timeline-title {

    color: #212529;

}


/* Stage Description */

.timeline-stage-description {

    font-size: 14px;

}


/* Activity History */

.activity-history {

    margin-top: 15px;

}


/* Mobile */

@media (max-width: 576px) {

    .timeline {

        padding-left: 45px;

    }


    .timeline-icon {

        left: -45px;

        width: 36px;

        height: 36px;

    }


    .timeline-title {

        font-size: 15px;

    }

}

</style>
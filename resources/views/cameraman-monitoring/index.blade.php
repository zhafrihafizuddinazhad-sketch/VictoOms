@extends('layouts.admin')

@section('content')

<div class="content-header">

    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center">

            <div>

                <h1 class="m-0">

                    <i class="fas fa-camera mr-2"></i>

                    Cameraman Monitoring

                </h1>

                <p class="text-muted mb-0">

                    Monitor cameraman assignments and current workload.

                </p>

            </div>

        </div>

    </div>

</div>


<section class="content">

    <div class="container-fluid">


        {{-- ================================================= --}}
        {{-- CAMERAMAN LIST --}}
        {{-- ================================================= --}}

        <div class="card">

            <div class="card-header">

                <h3 class="card-title">

                    <i class="fas fa-users mr-2"></i>

                    Cameramen

                </h3>

            </div>


            <div class="card-body p-0">

                @if($cameramen->count())

                    <div class="table-responsive">

                        <table class="table table-hover mb-0">

                            <thead>

                                <tr>

                                    <th style="width: 60px;">
                                        #
                                    </th>

                                    <th>
                                        Cameraman
                                    </th>

                                    <th>
                                        Email
                                    </th>

                                    <th>
                                        Active Tasks
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                @foreach($cameramen as $index => $cameraman)

                                    <tr>

                                        {{-- Number --}}

                                        <td>

                                            {{ $index + 1 }}

                                        </td>


                                        {{-- Name --}}

                                        <td>

                                            <div class="d-flex align-items-center">

                                                <div
                                                    class="mr-3"
                                                    style="
                                                        width:40px;
                                                        height:40px;
                                                        border-radius:50%;
                                                        background:#e9ecef;
                                                        display:flex;
                                                        align-items:center;
                                                        justify-content:center;
                                                    "
                                                >

                                                    <i class="fas fa-camera"></i>

                                                </div>


                                                <div>

                                                    <strong>

                                                        {{ $cameraman->name }}

                                                    </strong>

                                                </div>

                                            </div>

                                        </td>


                                        {{-- Email --}}

                                        <td>

                                            {{ $cameraman->email ?? '-' }}

                                        </td>


                                        {{-- Active Tasks --}}

                                        <td>

                                            @php

                                                $activeOrders = $cameraman->active_orders_count ?? 0;

                                            @endphp


                                            @if($activeOrders > 0)

                                                <span class="badge badge-warning">

                                                    {{ $activeOrders }}

                                                    {{ $activeOrders == 1 ? 'Task' : 'Tasks' }}

                                                </span>

                                            @else

                                                <span class="badge badge-success">

                                                    Available

                                                </span>

                                            @endif

                                        </td>


                                        {{-- Action --}}

                                        <td>

                                            @php

                                                $cameramanShowRoute = auth()->user()->hasRole('admin')
                                                    ? 'admin.cameramen.show'
                                                    : 'owner.cameramen.show';

                                            @endphp


                                            <a
                                                href="{{ route(
                                                    $cameramanShowRoute,
                                                    $cameraman
                                                ) }}"
                                                class="btn btn-primary btn-sm"
                                            >

                                                <i class="fas fa-eye mr-1"></i>

                                                View

                                            </a>

                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="text-center py-5">

                        <i
                            class="fas fa-camera fa-3x text-muted mb-3"
                        ></i>


                        <h5>

                            No Cameramen Found

                        </h5>


                        <p class="text-muted mb-0">

                            There are currently no users assigned
                            to the cameraman role.

                        </p>

                    </div>

                @endif

            </div>

        </div>

    </div>

</section>

@endsection
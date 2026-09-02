{{-- ================================================= --}}
{{-- DESIGNER WORKLOAD --}}
{{-- ================================================= --}}

<div class="card mt-3">

    <div class="card-header">

        <h4 class="mb-0">

            <i class="fas fa-users text-primary mr-2"></i>

            Designer Workload

        </h4>

    </div>


    <div class="card-body">

        @forelse($designers as $designer)

            <div class="mb-4">

                <div class="d-flex justify-content-between align-items-center mb-2">

                    <div>

                        <strong>
                            {{ $designer->name }}
                        </strong>

                    </div>


                    <span class="badge badge-primary">

                        {{ $designer->active_orders_count }}

                        Active Orders

                    </span>

                </div>


                @php

                    $maxOrders = max(
                        $designers->max('active_orders_count'),
                        1
                    );

                    $percentage =
                        ($designer->active_orders_count / $maxOrders) * 100;

                @endphp


                <div class="progress" style="height: 10px;">

                    <div
                        class="progress-bar"
                        role="progressbar"
                        style="width: {{ $percentage }}%;"
                        aria-valuenow="{{ $designer->active_orders_count }}"
                        aria-valuemin="0"
                        aria-valuemax="{{ $maxOrders }}">

                    </div>

                </div>

            </div>

        @empty

            <div class="text-center py-4 text-muted">

                <i class="fas fa-user-slash fa-2x mb-2"></i>

                <p class="mb-0">

                    No designers found.

                </p>

            </div>

        @endforelse

    </div>

</div>
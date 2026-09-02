
{{-- ================================================= --}}
{{-- JOB ORDERS --}}
{{-- ================================================= --}}

<div class="card mt-3">

    <div class="card-header d-flex justify-content-between align-items-center">

        <h4 class="mb-0">

            <i class="fas fa-file-alt"></i>

            Job Orders

        </h4>


        @if(auth()->user()->hasRole('designer'))

    <a
        href="{{ route('job-orders.create', $order) }}"
        class="btn btn-primary btn-sm">

        <i class="fas fa-plus"></i>
        Create Job Order

    </a>

@endif

    </div>


    <div class="card-body">

        @forelse($order->jobOrders as $jobOrder)

            <div class="border rounded p-3 mb-3">

                <div class="d-flex justify-content-between align-items-center">

                    <div>

                        <h5 class="mb-1">

                            <i class="fas fa-file-word text-primary"></i>

                            {{ $jobOrder->job_order_no }}

                        </h5>

                        <small class="text-muted">

                            Created by:

                            {{ $jobOrder->creator->name ?? 'Unknown' }}

                        </small>

                    </div>


                    <div>

    <span class="badge badge-warning">

    {{ $jobOrder->status }}

</span>


<a
    href="{{ route('job-orders.generate-word', $jobOrder) }}"
    class="btn btn-primary btn-sm ml-2">

    <i class="fas fa-file-word"></i>

    Generate Word

</a>


@if(
    auth()->user()->hasRole('designer') &&
    $jobOrder->created_by == auth()->id() &&
    $jobOrder->status === 'Draft'
)

    <form
        action="{{ route('job-orders.destroy', $jobOrder) }}"
        method="POST"
        class="d-inline ml-2"
        onsubmit="return confirm(
            'Are you sure you want to delete this Job Order? This action cannot be undone.'
        );">

        @csrf

        @method('DELETE')

        <button
            type="submit"
            class="btn btn-danger btn-sm">

            <i class="fas fa-trash"></i>

            Delete

        </button>

    </form>

@endif

</div>

                </div>


                <hr>


                <div class="row">

                    <div class="col-md-4">

                        <strong>
                            Total Quantity
                        </strong>

                        <p class="mb-0">

                            {{ $jobOrder->items->sum('quantity') }}

                            PCS

                        </p>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Created
                        </strong>

                        <p class="mb-0">

                            {{ $jobOrder->created_at->format('d M Y, h:i A') }}

                        </p>

                    </div>


                    <div class="col-md-4">

                        <strong>
                            Items
                        </strong>

                        <p class="mb-0">

                            {{ $jobOrder->items
                                ->pluck('item_name')
                                ->unique()
                                ->count() }}

                        </p>

                    </div>

                </div>


                <hr>


                <h6>
                    Size Breakdown
                </h6>


                <div class="table-responsive">

                    <table class="table table-sm table-bordered">

                        <thead>

                            <tr>

                                <th>
                                    Item
                                </th>

                                <th>
                                    Size
                                </th>

                                <th>
                                    Quantity
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            @foreach($jobOrder->items as $item)

                                <tr>

                                    <td>
                                        {{ $item->item_name }}
                                    </td>

                                    <td>
                                        {{ $item->size }}
                                    </td>

                                    <td>
                                        {{ $item->quantity }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        @empty

            <div class="text-center py-4 text-muted">

                <i class="fas fa-file-alt fa-2x mb-2"></i>

                <p class="mb-0">

                    No Job Order has been created yet.

                </p>

            </div>

        @endforelse

    </div>

</div>
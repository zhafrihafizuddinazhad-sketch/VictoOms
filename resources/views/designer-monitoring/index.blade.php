@extends('layouts.admin')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-3">

    <h1>Designer Monitoring</h1>

</div>

<div class="card">

    <div class="card-body">

        <table class="table table-bordered table-hover">

            <thead class="table-dark">

                <tr>

                    <th>Designer</th>
                    <th class="text-center">Active Orders</th>
                    <th class="text-center">Pending Approval</th>
                    <th class="text-center">Completed Orders</th>
                    <th class="text-center">Workload</th>
                    <th width="120">Action</th>

                </tr>

            </thead>

            <tbody>

                @forelse($designers as $designer)

                    <tr>

                        <td>{{ $designer->name }}</td>

                        <td class="text-center">
                            <span class="badge bg-primary">
                                {{ $designer->active_orders }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-warning text-dark">
                                {{ $designer->pending_approval }}
                            </span>
                        </td>

                        <td class="text-center">
                            <span class="badge bg-success">
                                {{ $designer->completed_orders }}
                            </span>
                        </td>

                        <td class="text-center">

    @if($designer->active_orders == 0)

        <span class="badge bg-secondary">
            Available
        </span>

    @elseif($designer->active_orders <= 3)

        <span class="badge bg-success">
            Moderate
        </span>

    @elseif($designer->active_orders <= 6)

        <span class="badge bg-warning text-dark">
            Busy
        </span>

    @else

        <span class="badge bg-danger">
            Overloaded
        </span>

    @endif

</td>

<td>

    <a href="{{ route('designer.monitoring.show', $designer) }}"
       class="btn btn-info btn-sm">

        <i class="fas fa-eye"></i>

    </a>

</td>

                    </tr>

                @empty

                    <tr>

                        <td colspan="6" class="text-center">

                            No designer found.

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>

@endsection
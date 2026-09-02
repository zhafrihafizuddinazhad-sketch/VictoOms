<div class="card">

    <div class="card-header">

        <h4>{{ $order->order_no }}</h4>

    </div>

    <div class="card-body">

        <div class="row">

            <div class="col-md-6">

                <strong>Customer</strong><br>

                {{ $order->customer->customer_name }}

            </div>

            <div class="col-md-6">

                <strong>Due Date</strong><br>

                {{ $order->due_date }}

            </div>

        </div>

        <hr>

        <strong>Status</strong>

<p>

    <span class="badge {{ $order->getStatusBadgeClass() }}">

        {{ $order->getStatusBadgeText() }}

    </span>

</p>

<strong>Designer</strong>
<p>{{ $order->designer->name ?? 'Not Assigned' }}</p>

<strong>Remarks</strong>
<p>{{ $order->remarks ?: '-' }}</p>

<strong>Delivery Method</strong>
<p>{{ $order->delivery_method }}</p>

    </div>

</div>
@extends('layouts.admin')

@section('content')
<style>
.sample-order-page .card{border:1px solid #e5eaf2;border-radius:14px;box-shadow:0 6px 22px rgba(21,44,79,.045);overflow:hidden}.sample-order-page .card-header{background:#fff;border-bottom:1px solid #edf0f5;padding:16px 20px}.sample-order-page .card-body{padding:20px}.sample-order-page .order-hero{background:linear-gradient(135deg,#142b51,#2458a6);border-radius:18px;color:#fff;padding:24px;margin-bottom:20px}.sample-order-page .order-stat{background:#fff;border:1px solid #e5eaf2;border-radius:12px;padding:16px;height:100%}.sample-order-page .order-stat .label{font-size:.75rem;text-transform:uppercase;letter-spacing:.06em;color:#74839a;font-weight:700}.sample-order-page .order-stat .value{font-weight:700;margin-top:5px;overflow-wrap:anywhere}.sample-order-page .item-card{background:#f8fafd;border:1px solid #e8edf4;border-radius:12px;padding:16px;height:100%}.sample-order-page .timeline-event{position:relative;border-left:2px solid #dbe5f2;padding:0 0 20px 20px;margin-left:8px}.sample-order-page .timeline-event:last-child{border-left-color:transparent;padding-bottom:0}.sample-order-page .timeline-dot{position:absolute;left:-7px;top:2px;width:12px;height:12px;border-radius:50%;background:#2864ae;border:2px solid #fff;box-shadow:0 0 0 1px #2864ae}
@media(max-width:575px){.sample-order-page .order-hero{padding:20px;border-radius:14px}.sample-order-page .card-header{padding:14px 16px}.sample-order-page .card-body{padding:16px}.sample-order-page .order-stat{padding:13px}}
</style>
<div class="container-fluid sample-order-page pb-4">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger"><strong>Some information needs attention.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

@php
$depositReady=$sampleOrder->depositIsSatisfied();
$orderPhotoTypes=$sampleOrder->photos->pluck('photo_type');
$hasHandoverPhoto=$sampleOrder->collection_method==='office'?$orderPhotoTypes->contains('before_handover'):($orderPhotoTypes->contains('before_delivery')||$orderPhotoTypes->contains('before_handover'));
$hasAfterReturnPhoto=$orderPhotoTypes->contains('after_return');
$statusLabel=ucwords(str_replace('_',' ',$sampleOrder->status));
$methodLabel=$sampleOrder->collection_method==='office'?'Office Pickup':'Lalamove';
$dateLabel=$sampleOrder->collection_method==='lalamove'?'Delivery Date':'Pickup Date';
$primaryDate=$sampleOrder->collection_method==='lalamove'?$sampleOrder->delivery_date:$sampleOrder->pickup_date;
@endphp
<header class="order-hero d-flex flex-wrap justify-content-between align-items-start gap-3">
<div><div class="small text-uppercase font-weight-bold" style="letter-spacing:.08em;opacity:.72">Sample Order</div><h1 class="h3 mt-2 mb-2">{{ $sampleOrder->order_number }}</h1><div class="d-flex flex-wrap align-items-center gap-2"><span class="badge badge-light px-2 py-1">{{ $statusLabel }}</span><span class="badge badge-light px-2 py-1">{{ $methodLabel }}</span>@if($sampleOrder->created_source==='customer')<span class="badge badge-info px-2 py-1">Customer submitted</span>@endif</div></div>
<a href="{{ route('sample-orders.index') }}" class="btn btn-light">← Sample Orders</a>
</header>

<section class="card mb-4 border-primary" aria-labelledby="next-step-heading"><div class="card-body d-flex flex-wrap align-items-center justify-content-between gap-3">
<div class="flex-grow-1"><div class="small text-uppercase text-primary font-weight-bold" id="next-step-heading">Next step</div>@if($sampleOrder->collection_method==='office')<div class="font-weight-bold mt-1">Office Pickup</div><p class="small text-muted mb-0">The customer collects the sample at Victo. Record each handover and return checkpoint below.</p>@else<div class="font-weight-bold mt-1">Lalamove delivery</div><p class="small text-muted mb-0">Coordinate delivery directly with the customer. VictoOMS does not book or track the driver.</p>@endif</div>
@if($sampleOrder->status==='pending_payment'&&$sampleOrder->collection_method==='office')@if($depositReady)<form action="{{ route('sample-orders.status.update',$sampleOrder) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="ready_for_collection"><button class="btn btn-primary" type="submit">Prepare for Pickup</button></form>@else<a href="#payment-panel" class="btn btn-primary">Record Payment</a>@endif
@elseif($sampleOrder->status==='pending_payment'&&$sampleOrder->collection_method!=='office')@if($depositReady&&$hasHandoverPhoto)<form action="{{ route('sample-orders.status.update',$sampleOrder) }}" method="POST" onsubmit="return confirm('Mark this sample as sent with Lalamove?')">@csrf @method('PATCH')<input type="hidden" name="status" value="in_transit"><button class="btn btn-primary" type="submit">Mark Sent with Lalamove</button></form>@elseif(!$depositReady)<a href="#payment-panel" class="btn btn-primary">Record Payment</a>@else<a href="#photo-evidence-heading" class="btn btn-primary">Upload Before Delivery</a>@endif
@elseif($sampleOrder->status==='ready_for_collection')@if($hasHandoverPhoto)<form action="{{ route('sample-orders.status.update',$sampleOrder) }}" method="POST" onsubmit="return confirm('Confirm that the customer has collected the sample?')">@csrf @method('PATCH')<input type="hidden" name="status" value="collected"><button class="btn btn-primary" type="submit">Confirm Customer Collection</button></form>@else<a href="#photo-evidence-heading" class="btn btn-primary">Upload Before Handover</a>@endif
@elseif($sampleOrder->collection_method==='office'&&$sampleOrder->status==='collected')<form action="{{ route('sample-orders.status.update',$sampleOrder) }}" method="POST">@csrf @method('PATCH')<input type="hidden" name="status" value="return_pending"><button class="btn btn-outline-primary" type="submit">Mark Return Expected</button></form>
@elseif($sampleOrder->status==='return_pending')<a href="#return-inspection" class="btn btn-primary">Record Return</a>
@elseif($sampleOrder->collection_method!=='office'&&in_array($sampleOrder->status,['in_transit','received'],true))<a href="{{ $customerPhotoUrl }}" class="btn btn-outline-primary" target="_blank" rel="noopener">Open Customer Upload Page</a>
@elseif($sampleOrder->status==='returned')@if($hasAfterReturnPhoto)<form action="{{ route('sample-orders.status.update',$sampleOrder) }}" method="POST" onsubmit="return confirm('Complete this sample order?')">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button class="btn btn-success" type="submit">Complete Sample Order</button></form>@else<a href="#photo-evidence-heading" class="btn btn-primary">Upload After Return</a>@endif
@elseif($sampleOrder->status==='completed')<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i>Sample order complete</span>@endif
</div></section>

<section class="row row-cols-2 row-cols-md-3 row-cols-xl-6 g-3 mb-4" aria-label="Order summary">
<div class="col"><div class="order-stat"><div class="label">Status</div><div class="value">{{ $statusLabel }}</div></div></div><div class="col"><div class="order-stat"><div class="label">Collection</div><div class="value">{{ $methodLabel }}</div></div></div><div class="col"><div class="order-stat"><div class="label">{{ $dateLabel }}</div><div class="value">{{ $primaryDate?->format('d M Y')??'—' }}</div></div></div><div class="col"><div class="order-stat"><div class="label">Return date</div><div class="value">{{ $sampleOrder->return_date?->format('d M Y')??'—' }}</div></div></div><div class="col"><div class="order-stat"><div class="label">Deposit</div><div class="value">RM {{ number_format((float)$sampleOrder->deposit_amount,2) }}</div><span class="badge {{ $sampleOrder->deposit_status==='paid'?'badge-success':'badge-warning' }}">{{ ucfirst($sampleOrder->deposit_status) }}</span></div></div><div class="col"><div class="order-stat"><div class="label">Created</div><div class="value">{{ $sampleOrder->created_at?->format('d M Y')??'—' }}</div></div></div>
</section>

<div class="row g-3 mb-4">
<div class="col-lg-6"><section class="card h-100"><div class="card-header"><strong><i class="far fa-user mr-2 text-primary"></i>Customer Information</strong></div><div class="card-body"><div class="h5 mb-1">{{ $sampleOrder->customer_name??'—' }}</div>@if($sampleOrder->customer?->company)<div class="text-muted mb-3">{{ $sampleOrder->customer->company }}</div>@endif<div class="row g-3"><div class="col-sm-6"><div class="small text-muted">Phone</div><div class="font-weight-semibold">{{ $sampleOrder->customer?->phone??'—' }}</div></div><div class="col-sm-6"><div class="small text-muted">Email</div><div class="font-weight-semibold text-break">{{ $sampleOrder->customer?->email??'—' }}</div></div></div></div></section></div>
<div class="col-lg-6"><section class="card h-100"><div class="card-header"><strong><i class="fas fa-map-marker-alt mr-2 text-primary"></i>Collection Information</strong></div><div class="card-body"><div class="mb-3"><div class="small text-muted">Collection method</div><div class="font-weight-semibold">{{ $methodLabel }}</div></div><div class="mb-3"><div class="small text-muted">{{ $dateLabel }}</div><div>{{ $primaryDate?->format('d M Y')??'—' }}</div></div>@if($sampleOrder->collection_method==='lalamove')<div class="mb-3"><div class="small text-muted">Delivery address</div><div class="text-break">{{ $sampleOrder->delivery_address??'—' }}</div></div>@endif<div><div class="small text-muted">Return date</div><div>{{ $sampleOrder->return_date?->format('d M Y')??'—' }}</div></div></div></section></div>
</div>
@if($sampleOrder->notes)<section class="card mb-4"><div class="card-header"><strong>Additional Notes</strong></div><div class="card-body">{{ $sampleOrder->notes }}</div></section>@endif
    {{-- Sample Items --}}
    <div class="card mb-4">

        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Sample Items</strong>
            <a href="{{ route('sample-items.create', $sampleOrder) }}" class="btn btn-sm btn-primary">+ Add Sample Item</a>
        </div>

        <div class="card-body">

            @if($sampleOrder->sampleItems && $sampleOrder->sampleItems->count())
                <div class="row g-3">
                    @foreach($sampleOrder->sampleItems as $index => $item)
                        <div class="col-12 col-lg-6"><article class="item-card">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-3"><div><span class="small text-muted">Item {{ $index + 1 }}</span><h3 class="h5 mb-0">{{ $item->item_type === 'others' ? 'Others' : ucfirst($item->item_type ?? '-') }}</h3>@if($item->item_type === 'others' && $item->sample_name)<div class="h6 text-primary font-weight-bold mt-1 mb-0">{{ $item->sample_name }}</div>@endif</div><span class="badge badge-primary">Qty {{ $item->quantity ?? '—' }}</span></div>
                            <div class="row g-2 small">
                                @if($item->fabric)<div class="col-sm-6"><span class="text-muted">Fabric</span><div class="font-weight-semibold">{{ $item->fabric }}</div></div>@endif
                                @if($item->sample?->sample_code)<div class="col-sm-6"><span class="text-muted">Physical sample</span><div>{{ $item->sample->sample_code }}</div></div>@endif
                                @if($item->description)<div class="col-12"><span class="text-muted">Description</span><div class="text-break">{{ $item->description }}</div></div>@endif
                            </div>
                            <div class="d-flex flex-wrap gap-2 mt-3"><a href="{{ route('sample-items.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a><form action="{{ route('sample-items.destroy', $item) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this sample item? This action cannot be undone.')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Remove</button></form></div>
                        </article></div>
                    @endforeach
                </div>

            @else

                <div class="text-center py-4">
                    <p class="fw-semibold mb-1">No sample items added yet.</p>
                    <p class="text-muted mb-3">Add the first sample item to this order.</p>
                    <a href="{{ route('sample-items.create', $sampleOrder) }}" class="btn btn-primary">+ Add Sample Item</a>
                </div>

            @endif

        </div>

    </div>


    {{-- Original sample photos, separate from workflow evidence --}}
    <section class="card mb-4" aria-labelledby="sample-photos-heading">
        <div class="card-header"><strong id="sample-photos-heading">Original Sample Photos</strong></div>
        <div class="card-body">
            <p class="text-muted small">Original photos showing the sample provided to the customer.</p>
            @if($samplePhotos->isNotEmpty())
                <div class="row g-3">
                    @foreach($samplePhotos as $photo)
                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                            <div class="border rounded p-2 h-100">
                                <a href="{{ route('sample-photos.show', [$sampleOrder, $photo]) }}" target="_blank" rel="noopener" aria-label="Open original sample photo">
                                    <img src="{{ route('sample-photos.show', [$sampleOrder, $photo]) }}" alt="Original sample photo" class="img-fluid rounded w-100" style="height:130px;object-fit:cover">
                                </a>
                                <div class="d-flex justify-content-between align-items-center gap-1 mt-2"><span class="small text-muted">{{ $photo->created_at?->format('d M Y') }}</span>
                                    <form action="{{ route('sample-photos.destroy', [$sampleOrder, $photo]) }}" method="POST" onsubmit="return confirm('Delete this sample photo?')">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-outline-danger">Delete</button></form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="mb-0 text-muted">No original sample photos were added during order creation.</p>
            @endif
        </div>
    </section>


    <div class="row g-4 mb-4">
        <div class="col-lg-7" id="payment-panel">
            <div class="card h-100" id="payment-panel-card">
                <div class="card-header d-flex justify-content-between align-items-center"><strong>Payment / Deposit</strong><span class="badge {{ $sampleOrder->deposit_status === 'paid' ? 'bg-success' : ($sampleOrder->deposit_status === 'refunded' ? 'bg-info text-dark' : 'bg-warning text-dark') }}">{{ ucwords(str_replace('_', ' ', $sampleOrder->deposit_status)) }}</span></div>
                <div class="card-body">
                    <div class="mb-3"><div class="small text-muted">Deposit required</div><div class="fs-4 fw-semibold">RM {{ number_format((float) $sampleOrder->deposit_amount, 2) }}</div></div>
                    <details>
                        <summary class="btn btn-primary">+ Record Payment</summary>
                        <form action="{{ route('sample-orders.payments.store', $sampleOrder) }}" method="POST" class="border rounded p-3 mt-3" onsubmit="return confirmSamplePayment(this)">
                            @csrf
                            <div class="row g-3">
                                <div class="col-sm-6"><label for="new-payment-amount" class="form-label">Amount (RM) <span class="text-danger">*</span></label><input id="new-payment-amount" name="amount" type="number" step="0.01" min="0" required value="{{ old('amount', $sampleOrder->deposit_amount) }}" class="form-control @error('amount') is-invalid @enderror">@error('amount')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-sm-6"><label for="new-payment-method" class="form-label">Payment method <span class="text-danger">*</span></label><select id="new-payment-method" name="payment_method" required class="form-select @error('payment_method') is-invalid @enderror"><option value="">Choose method</option><option value="cash" @selected(old('payment_method') === 'cash')>Cash</option><option value="bank_transfer" @selected(old('payment_method') === 'bank_transfer')>Bank Transfer</option><option value="online" @selected(old('payment_method') === 'online')>Online</option></select>@error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-sm-6"><label for="new-payment-status" class="form-label">Status <span class="text-danger">*</span></label><select id="new-payment-status" name="status" required class="form-select @error('status') is-invalid @enderror"><option value="pending" @selected(old('status', 'pending') === 'pending')>Pending</option><option value="paid" @selected(old('status') === 'paid')>Paid</option><option value="failed" @selected(old('status') === 'failed')>Failed</option><option value="refunded" @selected(old('status') === 'refunded')>Refunded</option></select>@error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-sm-6"><label for="new-payment-date" class="form-label">Paid at <span class="text-muted">(optional)</span></label><input id="new-payment-date" name="paid_at" type="datetime-local" value="{{ old('paid_at') }}" class="form-control @error('paid_at') is-invalid @enderror">@error('paid_at')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                                <div class="col-12"><label for="new-payment-notes" class="form-label">Notes <span class="text-muted">(optional)</span></label><textarea id="new-payment-notes" name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror">{{ old('notes') }}</textarea>@error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Save Payment</button>
                        </form>
                    </details>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header"><strong>Payment History</strong></div>
                <div class="card-body">
                    @if($sampleOrder->payments->isNotEmpty())
                        <div class="table-responsive"><table class="table table-sm align-middle mb-0">
                            <thead><tr><th>Date</th><th>Amount</th><th>Method / Status</th><th></th></tr></thead>
                            <tbody>
                                @foreach($sampleOrder->payments as $payment)
                                    @php
                                        $badgeClass = match($payment->status) {
                                            'paid' => 'bg-success',
                                            'failed' => 'bg-danger',
                                            'refunded' => 'bg-info text-dark',
                                            default => 'bg-warning text-dark',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="text-nowrap">{{ ($payment->paid_at ?? $payment->created_at)?->format('d M Y') ?? '—' }}<div class="small text-muted">{{ ($payment->paid_at ?? $payment->created_at)?->format('h:i A') }}</div></td>
                                        <td class="text-nowrap">RM {{ number_format((float) $payment->amount, 2) }}</td>
                                        <td><div>{{ ucwords(str_replace('_', ' ', $payment->payment_method ?? '—')) }}</div><span class="badge {{ $badgeClass }}">{{ ucfirst($payment->status) }}</span>@if($payment->confirmedBy)<div class="small text-muted">Confirmed by {{ $payment->confirmedBy->name }}</div>@endif</td>
                                        <td><details><summary class="btn btn-sm btn-outline-secondary">Update</summary>
                                            <form action="{{ route('sample-orders.payments.update', [$sampleOrder, $payment]) }}" method="POST" class="border rounded p-3 mt-2" onsubmit="return confirmSamplePayment(this)">
                                                @csrf @method('PUT')
                                                <label class="form-label" for="payment-amount-{{ $payment->id }}">Amount (RM)</label><input id="payment-amount-{{ $payment->id }}" name="amount" type="number" step="0.01" min="0" required value="{{ $payment->amount }}" class="form-control mb-2">
                                                <label class="form-label" for="payment-method-{{ $payment->id }}">Method</label><select id="payment-method-{{ $payment->id }}" name="payment_method" required class="form-select mb-2"><option value="cash" @selected($payment->payment_method === 'cash')>Cash</option><option value="bank_transfer" @selected($payment->payment_method === 'bank_transfer')>Bank Transfer</option><option value="online" @selected($payment->payment_method === 'online')>Online</option></select>
                                                <label class="form-label" for="payment-status-{{ $payment->id }}">Status</label><select id="payment-status-{{ $payment->id }}" name="status" required class="form-select mb-2"><option value="pending" @selected($payment->status === 'pending')>Pending</option><option value="paid" @selected($payment->status === 'paid')>Paid</option><option value="failed" @selected($payment->status === 'failed')>Failed</option><option value="refunded" @selected($payment->status === 'refunded')>Refunded</option></select>
                                                <label class="form-label" for="payment-date-{{ $payment->id }}">Paid at</label><input id="payment-date-{{ $payment->id }}" name="paid_at" type="datetime-local" value="{{ $payment->paid_at?->format('Y-m-d\\TH:i') }}" class="form-control mb-2">
                                                <label class="form-label" for="payment-notes-{{ $payment->id }}">Notes</label><textarea id="payment-notes-{{ $payment->id }}" name="notes" rows="2" class="form-control mb-2">{{ $payment->notes }}</textarea>
                                                <button type="submit" class="btn btn-sm btn-primary">Save Changes</button>
                                            </form>
                                        </details></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table></div>
                    @else
                        <div class="text-center py-4"><p class="text-muted mb-0">No payment records yet.</p></div>
                    @endif
                </div>
            </div>
        </div>
        <script>
            function confirmSamplePayment(form) {
                const status = form.elements.status.value;
                if (status === 'paid') return confirm('Confirm that this payment has been received? The authenticated staff member and current time will be recorded.');
                if (status === 'refunded') return confirm('Mark this payment as refunded? Confirm the refund has already been issued.');
                return confirm('Save these payment details?');
            }
        </script>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header"><strong>Return & Inspection</strong></div>
                <div class="card-body">
                    @if($sampleOrder->sampleReturn)
                        <div class="mb-2"><span class="text-muted">Condition</span><div class="fw-semibold">{{ $sampleOrder->sampleReturn->condition === 'lost' ? 'Missing' : ucfirst($sampleOrder->sampleReturn->condition) }}</div></div>
                        <div class="mb-2"><span class="text-muted">Returned</span><div>{{ $sampleOrder->sampleReturn->returned_at?->format('d M Y, h:i A') ?? 'Not recorded' }}</div></div>
                        @if($sampleOrder->sampleReturn->deposit_action)<div class="mb-2"><span class="text-muted">Deposit action</span><div>{{ ucwords(str_replace('_', ' ', $sampleOrder->sampleReturn->deposit_action)) }}</div></div>@endif
                        @if($sampleOrder->sampleReturn->receivedBy)<div class="mb-2"><span class="text-muted">Received by</span><div>{{ $sampleOrder->sampleReturn->receivedBy->name }}</div></div>@endif
                        @if($sampleOrder->sampleReturn->damage_description)<div class="mb-2"><span class="text-muted">Inspection details</span><div>{{ $sampleOrder->sampleReturn->damage_description }}</div></div>@endif
                        @if($sampleOrder->sampleReturn->notes)<div class="mb-3"><span class="text-muted">Notes</span><div>{{ $sampleOrder->sampleReturn->notes }}</div></div>@endif
                    @else <p class="text-muted mb-3">No return has been recorded.</p> @endif

                    @if($sampleOrder->sampleReturn || $sampleOrder->status === 'return_pending')
                        <details id="return-inspection" @if($sampleOrder->status === 'return_pending') open @endif>
                            <summary class="btn btn-sm {{ $sampleOrder->sampleReturn ? 'btn-outline-secondary' : 'btn-primary' }}">{{ $sampleOrder->sampleReturn ? 'Update Inspection' : 'Record Sample Return' }}</summary>
                            <form action="{{ route('sample-orders.return.store', $sampleOrder) }}" method="POST" class="border rounded p-3 mt-3" onsubmit="return confirm('Save the sample return and inspection?')">
                                @csrf
                                <div class="form-group"><label for="return-datetime">Returned at</label><input id="return-datetime" type="datetime-local" name="returned_at" value="{{ old('returned_at', $sampleOrder->sampleReturn?->returned_at?->format('Y-m-d\\TH:i') ?? now()->format('Y-m-d\\TH:i')) }}" class="form-control" required></div>
                                <div class="form-group"><label for="return-condition">Condition <span class="text-danger">*</span></label><select id="return-condition" name="condition" class="form-control" required><option value="good" @selected(old('condition', $sampleOrder->sampleReturn?->condition) === 'good')>Good</option><option value="damaged" @selected(old('condition', $sampleOrder->sampleReturn?->condition) === 'damaged')>Damaged</option><option value="lost" @selected(old('condition', $sampleOrder->sampleReturn?->condition) === 'lost')>Missing</option><option value="other" @selected(old('condition', $sampleOrder->sampleReturn?->condition) === 'other')>Other</option></select></div>
                                <div class="form-group"><label for="deposit-action">Deposit action</label><select id="deposit-action" name="deposit_action" class="form-control"><option value="">No action selected</option><option value="refund" @selected(old('deposit_action', $sampleOrder->sampleReturn?->deposit_action) === 'refund')>Refund</option><option value="partial_refund" @selected(old('deposit_action', $sampleOrder->sampleReturn?->deposit_action) === 'partial_refund')>Partial refund</option><option value="deduct" @selected(old('deposit_action', $sampleOrder->sampleReturn?->deposit_action) === 'deduct')>Deduct</option><option value="hold" @selected(old('deposit_action', $sampleOrder->sampleReturn?->deposit_action) === 'hold')>Hold</option><option value="forfeit" @selected(old('deposit_action', $sampleOrder->sampleReturn?->deposit_action) === 'forfeit')>Forfeit</option></select></div>
                                <div class="form-group"><label for="return-damage">Damage / missing details</label><textarea id="return-damage" name="damage_description" rows="2" class="form-control" placeholder="Describe damage or missing items">{{ old('damage_description', $sampleOrder->sampleReturn?->damage_description) }}</textarea></div>
                                <div class="form-group"><label for="return-notes">Inspection notes</label><textarea id="return-notes" name="notes" rows="2" class="form-control">{{ old('notes', $sampleOrder->sampleReturn?->notes) }}</textarea></div>
                                <button class="btn btn-primary" type="submit">Save Return Inspection</button>
                            </form>
                        </details>
                    @else
                        <p class="small text-muted mb-0">Return recording becomes available after the customer collects or receives the sample.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <section class="mb-4" aria-labelledby="photo-evidence-heading">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <div><h2 class="h5 mb-1" id="photo-evidence-heading">Photo Evidence</h2><p class="text-muted small mb-0">Upload JPG, PNG, or WebP images. Up to 10 photos, 5 MB each.</p></div>
        </div>
        @if($customerPhotoUrl)
            <div class="alert alert-info d-flex flex-wrap align-items-center justify-content-between gap-2"><span>Share this secure link with the customer for receipt and return photos.</span><a href="{{ $customerPhotoUrl }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Customer Photo Upload Link</a></div>
        @endif
        <div class="row g-3">
            @foreach($photoCheckpoints as $checkpoint)
                @php $key = $checkpoint['key']; @endphp
                <div class="col-12">
                    <div class="card shadow-sm">
                        <div class="card-header bg-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div><strong>{{ $checkpoint['label'] }}</strong><div class="small text-muted">{{ $checkpoint['description'] }}</div></div>
                            <span class="badge bg-light text-dark border">{{ $photosByCheckpoint->get($key, collect())->count() }} {{ $photosByCheckpoint->get($key, collect())->count() === 1 ? 'photo' : 'photos' }}</span>
                        </div>
                        <div class="card-body">
                            @if($photosByCheckpoint->get($key, collect())->isNotEmpty())
                                <div class="row g-3 mb-3">
                                    @foreach($photosByCheckpoint->get($key) as $photo)
                                        <div class="col-6 col-sm-4 col-md-3 col-xl-2">
                                            <div class="border rounded p-2 h-100">
                                                <a href="{{ route('sample-photos.show', [$sampleOrder, $photo]) }}" target="_blank" rel="noopener" aria-label="Open full image for {{ $checkpoint['label'] }}">
                                                    <img src="{{ route('sample-photos.show', [$sampleOrder, $photo]) }}" alt="{{ $photo->notes ?: $checkpoint['label'].' evidence' }}" class="img-fluid rounded w-100" style="height: 130px; object-fit: cover;">
                                                </a>
                                                @if($photo->notes)<div class="small text-truncate mt-2" title="{{ $photo->notes }}">{{ $photo->notes }}</div>@endif
                                                <div class="d-flex justify-content-between align-items-center gap-1 mt-2">
                                                    <span class="small text-muted">{{ $photo->created_at?->format('d M Y') }}</span>
                                                    <form action="{{ route('sample-photos.destroy', [$sampleOrder, $photo]) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this photo?')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete photo" title="Delete photo">Delete</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted small mb-3">No photos uploaded for this checkpoint yet.</p>
                            @endif

                            @if($checkpoint['customer'])
                                <a href="{{ $customerPhotoUrl }}" class="btn btn-sm btn-outline-primary" target="_blank" rel="noopener">Customer uploads this checkpoint</a>
                            @elseif(! $checkpoint['available'])
                                <p class="small text-muted mb-0">
                                    @if($key === 'before_handover') Available after the deposit is ready and the order is prepared for pickup.
                                    @elseif($key === 'before_delivery') Available after the deposit is paid, before the sample is marked as sent.
                                    @else Available after staff records the physical return.
                                    @endif
                                </p>
                            @else
                                <form action="{{ route('sample-photos.store', $sampleOrder) }}" method="POST" enctype="multipart/form-data" class="row g-2 align-items-end">
                                    @csrf
                                    <input type="hidden" name="photo_type" value="{{ $key }}">
                                    <div class="col-md-5"><label for="photos-{{ $key }}" class="form-label">Upload photos</label><input type="file" name="photos[]" id="photos-{{ $key }}" class="form-control" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" multiple required></div>
                                    <div class="col-md-5"><label for="notes-{{ $key }}" class="form-label">Caption <span class="text-muted">(optional)</span></label><input type="text" name="notes" id="notes-{{ $key }}" class="form-control" maxlength="1000" placeholder="Add a note for these photos"></div>
                                    <div class="col-md-2"><button type="submit" class="btn btn-outline-primary w-100">Upload Photos</button></div>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <div class="card mb-5">
        <div class="card-header"><strong>Order Timeline</strong></div>
        <div class="card-body">
            @php
                $timelineEvents = collect([['label' => 'Order Created', 'at' => $sampleOrder->created_at]]);
                $eventLabels = [
                    'prepared_for_pickup' => 'Prepared for Pickup',
                    'sent_with_lalamove' => 'Sent with Lalamove',
                    'customer_collected' => 'Customer Collection',
                    'return_expected' => 'Return Expected',
                    'order_completed' => 'Order Completed',
                ];
                foreach ($sampleOrder->events->sortBy('created_at') as $workflowEvent) {
                    if (isset($eventLabels[$workflowEvent->event_key])) {
                        $timelineEvents->push(['label' => $eventLabels[$workflowEvent->event_key], 'at' => $workflowEvent->created_at]);
                    }
                }
                $paidPayment = $sampleOrder->payments->where('status', 'paid')->sortBy('paid_at')->first();
                if ($paidPayment) $timelineEvents->push(['label' => 'Deposit Received', 'at' => $paidPayment->paid_at ?? $paidPayment->created_at]);
                $handoverPhoto = $photosByCheckpoint->get($sampleOrder->collection_method === 'office' ? 'before_handover' : 'before_delivery', collect())->sortBy('created_at')->first();
                if ($handoverPhoto) $timelineEvents->push(['label' => $sampleOrder->collection_method === 'office' ? 'Before Handover Photo' : 'Before Delivery Photo', 'at' => $handoverPhoto->created_at]);
                $receivedPhoto = $photosByCheckpoint->get('customer_received', collect())->sortBy('created_at')->first();
                if ($receivedPhoto) $timelineEvents->push(['label' => 'Customer Received Photo', 'at' => $receivedPhoto->created_at]);
                $beforeReturnPhoto = $photosByCheckpoint->get('before_return', collect())->sortBy('created_at')->first();
                if ($beforeReturnPhoto) $timelineEvents->push(['label' => 'Before Return Photo', 'at' => $beforeReturnPhoto->created_at]);
                if ($sampleOrder->sampleReturn) {
                    $timelineEvents->push(['label' => 'Return Recorded', 'at' => $sampleOrder->sampleReturn->returned_at]);
                    $inspectionCondition = $sampleOrder->sampleReturn->condition === 'lost' ? 'Missing' : ucfirst($sampleOrder->sampleReturn->condition);
                    $timelineEvents->push(['label' => 'Inspection · '.$inspectionCondition, 'at' => $sampleOrder->sampleReturn->returned_at]);
                }
                $afterReturnPhoto = $photosByCheckpoint->get('after_return', collect())->sortBy('created_at')->first();
                if ($afterReturnPhoto) $timelineEvents->push(['label' => 'After Return Photo', 'at' => $afterReturnPhoto->created_at]);
                if ($sampleOrder->status === 'completed' && ! $sampleOrder->events->contains('event_key', 'order_completed')) {
                    $timelineEvents->push(['label' => 'Order Completed', 'at' => null]);
                }
                $timelineEvents = $timelineEvents->sortBy(fn ($event) => $event['at']?->timestamp ?? PHP_INT_MAX)->values();
            @endphp
            <div class="timeline">
                @foreach($timelineEvents as $event)
                    <div class="timeline-event">
                        <span class="timeline-dot" aria-hidden="true"></span>
                        <div><strong>{{ $event['label'] }}</strong>@if($event['at'])<div class="small text-muted mt-1">{{ $event['at']->format('d M Y, h:i A') }}</div>@endif</div>
                    </div>
                @endforeach
            </div>
            <p class="small text-muted mb-0">Current order status: {{ ucwords(str_replace('_', ' ', $sampleOrder->status)) }}</p>
        </div>
    </div>

</div>

@endsection

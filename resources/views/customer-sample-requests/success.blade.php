@extends('layouts.customer', ['title' => 'Sample Request Submitted', 'subtitle' => 'Sample Request'])

@section('content')
<style>
    .success-wrap{max-width:620px;margin:9vh auto 0}.success-card{border:0;border-radius:22px;box-shadow:0 16px 48px rgba(21,44,79,.1)}.success-icon{width:72px;height:72px;margin:auto;border-radius:50%;background:#e9f8ef;color:#168345;display:grid;place-items:center;font-size:34px}.success-card .btn{min-height:52px;border-radius:11px;font-weight:700}
    @media(max-width:575px){.success-wrap{margin:4vh auto 0}.success-card{border-radius:18px}}
</style>
<div class="success-wrap">
    <section class="card success-card text-center"><div class="card-body p-4 p-sm-5">
        <div class="success-icon mb-4" aria-hidden="true">✓</div>
        <div class="text-uppercase small fw-bold text-muted" style="letter-spacing:.1em">Request received</div>
        <h1 class="h3 mt-2">Sample request submitted</h1>
        <p class="text-muted mt-3">Thank you, {{ $sampleOrder->customer_name }}. Your request has been submitted to Victo. Our team will confirm availability and discuss the deposit with you.</p>
        <div class="bg-light rounded-3 p-3 my-4"><div class="small text-muted">Request reference</div><div class="fs-5 fw-bold">{{ $sampleOrder->order_number }}</div><div class="small text-muted mt-1">Deposit status: Pending</div></div>
        @if($whatsappUrl)
            <a class="btn btn-success w-100 d-flex align-items-center justify-content-center" href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer">Continue to WhatsApp</a>
            <p class="small text-muted mt-3 mb-0">WhatsApp will open with your request summary ready to send.</p>
        @else
            <div class="alert alert-info text-start mb-0">Your request is saved. WhatsApp contact is not configured yet, so please contact Victo directly and share your reference.</div>
        @endif
    </div></section>
</div>
@endsection

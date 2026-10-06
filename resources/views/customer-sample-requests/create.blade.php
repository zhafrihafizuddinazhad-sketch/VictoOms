@extends('layouts.customer', ['title' => 'Request a Sample', 'subtitle' => 'Sample Request'])

@section('content')
<div class="mx-auto" style="max-width:820px">
    <header class="mb-4 p-4 p-sm-5 text-white" style="background:linear-gradient(135deg,#142b51,#2458a6);border-radius:18px">
        <div class="small text-uppercase font-weight-bold" style="letter-spacing:.09em;opacity:.75">Victo · Sample Management</div>
        <h1 class="h3 mt-2 mb-2">Request a sample</h1>
        <p class="mb-0" style="opacity:.8">Tell us what you need. Our team will confirm availability and deposit details with you on WhatsApp.</p>
    </header>
    @if($errors->any())
        <div class="alert alert-danger rounded-lg" role="alert"><strong>Please check your request.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form id="sample-request-form" method="POST" action="{{ route('customer.sample-request.store') }}" data-loading="off">
        @csrf
        <div id="request-fields">
            @include('sample-orders.partials.request-fields')
            <div class="d-grid mb-4"><button class="btn btn-primary btn-lg" type="submit">Review sample request</button></div>
        </div>
        <section id="request-review" class="card request-card" hidden style="border:1px solid #e5eaf2;border-radius:16px;box-shadow:0 8px 28px rgba(21,44,79,.05);margin-bottom:16px">
            <div class="card-header bg-white border-0 pt-4 px-4"><span class="small text-uppercase text-muted font-weight-bold">05 · Review and submit</span><br><strong>Check your request</strong></div>
            <div class="card-body px-4 pb-4"><p class="text-muted">Make sure these details are correct before sending your request to Victo.</p><div id="review-summary"></div>
                <div class="d-flex flex-column flex-sm-row gap-2 mt-4"><button class="btn btn-outline-secondary flex-fill" type="button" id="edit-request">Back and edit</button><button class="btn btn-primary flex-fill" type="submit" id="confirm-submit">Submit sample request</button></div>
            </div>
        </section>
    </form>
</div>
<style>.review-line{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #edf0f5}.review-line:last-child{border:0}.review-value{font-weight:600;text-align:right;overflow-wrap:anywhere}@media(max-width:575px){.review-line{display:block}.review-value{display:block;text-align:left;margin-top:3px}}</style>
<script>
(() => {
    const form = document.getElementById('sample-request-form');
    const fields = document.getElementById('request-fields');
    const review = document.getElementById('request-review');
    let reviewing = false;
    const val = id => document.getElementById(id)?.value.trim() ?? '';
    const escapeHtml = value => { const node = document.createElement('span'); node.textContent = value || '—'; return node.innerHTML; };
    const displayDate = value => value ? new Date(value + 'T00:00:00').toLocaleDateString('en-MY',{day:'2-digit',month:'short',year:'numeric'}) : '—';
    const line = (label, value) => `<div class="review-line"><span class="text-muted">${label}</span><span class="review-value">${escapeHtml(value)}</span></div>`;
    form.addEventListener('submit', event => {
        if (reviewing) {
            delete form.dataset.loading;
            return;
        }
        event.preventDefault(); if (!form.reportValidity()) return;
        const method = val('collection_method');
        const parts = [line('Full name',val('full_name')),line('Phone',val('phone')),line('Company',val('company')),line('Email',val('email')),line('Collection',method === 'office' ? 'Office pickup' : 'Lalamove delivery')];
        if (method === 'office') parts.push(line('Pickup date',displayDate(val('pickup_date')))); else { parts.push(line('Delivery date',displayDate(val('delivery_date')))); parts.push(line('Delivery address',val('delivery_address'))); }
        parts.push(line('Return date',displayDate(val('return_date'))));
        form.querySelectorAll('[data-sample-row]').forEach((row,index) => { const get = suffix => row.querySelector(`[name$="[${suffix}]"]`)?.value.trim() ?? ''; const type=get('item_type'); const label=type==='others' ? `Others · ${get('sample_name')}` : type.charAt(0).toUpperCase()+type.slice(1); parts.push(line(`Sample ${index+1}`,`${label} · Qty ${get('quantity')}${get('fabric') ? ' · '+get('fabric') : ''}${get('description') ? ' · '+get('description') : ''}`)); });
        parts.push(line('Additional notes',val('notes'))); document.getElementById('review-summary').innerHTML=parts.join(''); fields.hidden=true; review.hidden=false; reviewing=true; review.scrollIntoView({behavior:'smooth',block:'start'});
    });
    document.getElementById('edit-request').addEventListener('click',()=>{review.hidden=true;fields.hidden=false;reviewing=false;document.getElementById('full_name').focus();});
})();
</script>
@endsection

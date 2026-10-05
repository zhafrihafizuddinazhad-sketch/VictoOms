@extends('layouts.customer', ['title' => 'Request a Sample', 'subtitle' => 'Sample Request'])

@section('content')
<style>
    .request-shell{max-width:760px;margin:0 auto}.request-hero{background:linear-gradient(135deg,#142b51,#2458a6);color:#fff;border-radius:20px;padding:26px 24px;margin-bottom:20px}.request-card{border:1px solid #e5eaf2;border-radius:16px;box-shadow:0 8px 28px rgba(21,44,79,.05);margin-bottom:16px}.request-card .card-header{background:#fff;border:0;padding:20px 20px 0;font-weight:700}.request-card .card-header:not(.d-flex){display:block}.request-card .card-body{padding:18px 20px 22px}.request-shell .form-control,.request-shell .form-select{min-height:48px;border-radius:10px}.request-shell textarea.form-control{min-height:105px}.request-shell .btn{min-height:48px;border-radius:10px;font-weight:600}.section-kicker{display:block;font-size:.76rem;text-transform:uppercase;letter-spacing:.09em;color:#7c8ba2;font-weight:700}.sample-row{background:#f7f9fc;border:1px solid #e8edf4;border-radius:12px;padding:14px;margin-bottom:12px}.review-line{display:flex;justify-content:space-between;gap:16px;padding:10px 0;border-bottom:1px solid #edf0f5}.review-line:last-child{border:0}.review-value{font-weight:600;text-align:right;overflow-wrap:anywhere}.request-help{color:#6e7d92;font-size:.9rem}
    @media(max-width:575px){.request-hero{padding:22px 18px;border-radius:16px}.request-card .card-header{padding:18px 16px 0}.request-card .card-body{padding:16px}.sample-row{padding:12px}.review-line{display:block}.review-value{display:block;text-align:left;margin-top:3px}}
</style>
<div class="request-shell">
    <header class="request-hero">
        <div class="section-kicker text-white-50">Victo · Sample Management</div>
        <h1 class="h3 mt-2 mb-2">Request a sample</h1>
        <p class="mb-0 text-white-50">Tell us what you need. The Victo team will confirm availability and deposit details with you on WhatsApp.</p>
    </header>

    @if($errors->any())
        <div class="alert alert-danger rounded-3" role="alert"><strong>Please check your request.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <form id="sample-request-form" method="POST" action="{{ route('customer.sample-request.store') }}">
        @csrf
        <div id="request-fields">
            <section class="card request-card">
                <div class="card-header"><span class="section-kicker">01 · Your details</span><strong>Your contact information</strong></div>
                <div class="card-body">
                    <div class="mb-3"><label class="form-label" for="full_name">Full name <span class="text-danger">*</span></label><input class="form-control" id="full_name" name="full_name" value="{{ old('full_name') }}" autocomplete="name" required maxlength="255"></div>
                    <div class="row g-3">
                        <div class="col-sm-6"><label class="form-label" for="phone">Malaysian phone number <span class="text-danger">*</span></label><input class="form-control" id="phone" name="phone" value="{{ old('phone') }}" type="tel" autocomplete="tel" inputmode="tel" placeholder="012 345 6789" required maxlength="30"><div class="form-text">You can include +60, spaces or hyphens.</div></div>
                        <div class="col-sm-6"><label class="form-label" for="company">Company <span class="text-muted">(optional)</span></label><input class="form-control" id="company" name="company" value="{{ old('company') }}" autocomplete="organization" maxlength="255"></div>
                    </div>
                    <div class="mt-3"><label class="form-label" for="email">Email <span class="text-muted">(optional)</span></label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255"></div>
                </div>
            </section>

            <section class="card request-card">
                <div class="card-header d-flex justify-content-between align-items-center gap-3"><div><span class="section-kicker">02 · What you need</span><strong>Sample items</strong></div><button type="button" class="btn btn-outline-primary btn-sm" id="add-sample-item">+ Add another sample</button></div>
                <div class="card-body">
                    <div class="request-help mb-3">Add at least one item. Item types follow the current Victo sample catalogue.</div>
                    <div id="sample-items-list">
                        @foreach(old('items', [['item_type' => 'shirt', 'quantity' => 1, 'fabric' => '', 'description' => '']]) as $index => $item)
                        <div class="sample-row">
                            <div class="row g-3">
                                <div class="col-12 col-sm-5"><label class="form-label" for="item-type-{{ $index }}">Item type <span class="text-danger">*</span></label><select class="form-select" id="item-type-{{ $index }}" name="items[{{ $index }}][item_type]" required><option value="shirt" @selected(($item['item_type'] ?? '') === 'shirt')>Shirt</option><option value="short" @selected(($item['item_type'] ?? '') === 'short')>Short</option></select></div>
                                <div class="col-12 col-sm-3"><label class="form-label">Quantity <span class="text-danger">*</span></label><input class="form-control" type="number" min="1" max="1000" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required></div>
                                <div class="col-12 col-sm-4"><label class="form-label">Fabric</label><input class="form-control" name="items[{{ $index }}][fabric]" value="{{ $item['fabric'] ?? '' }}" maxlength="255" placeholder="e.g. Microfiber"></div>
                                <div class="col-12"><label class="form-label">Description / requirements</label><textarea class="form-control" name="items[{{ $index }}][description]" rows="2" maxlength="5000" placeholder="Colour, style or other details">{{ $item['description'] ?? '' }}</textarea></div>
                                <div class="col-12 text-end"><button class="btn btn-link text-danger p-0 remove-sample-item" type="button">Remove item</button></div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @error('items')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </section>

            <section class="card request-card">
                <div class="card-header"><span class="section-kicker">03 · How to receive it</span><strong>Collection method</strong></div>
                <div class="card-body">
                    <label class="form-label" for="collection_method">Choose one <span class="text-danger">*</span></label>
                    <select class="form-select" id="collection_method" name="collection_method" required><option value="">Select collection method</option><option value="office" @selected(old('collection_method') === 'office')>Office pickup</option><option value="lalamove" @selected(old('collection_method') === 'lalamove')>Lalamove delivery</option></select>
                    <div class="form-text">Lalamove delivery will be arranged separately with the Victo team via WhatsApp.</div>
                </div>
            </section>

            <section class="card request-card">
                <div class="card-header"><span class="section-kicker">04 · Dates and details</span><strong>Plan your sample</strong></div>
                <div class="card-body">
                    <div id="office-fields" class="conditional-fields">
                        <div class="mb-3"><label class="form-label" for="pickup_date">Pickup date <span class="text-danger">*</span></label><input class="form-control" type="date" id="pickup_date" name="pickup_date" value="{{ old('pickup_date') }}"><div class="form-text">Please select the date you plan to collect the sample from Victo.</div></div>
                    </div>
                    <div id="lalamove-fields" class="conditional-fields" hidden>
                        <div class="mb-3"><label class="form-label" for="delivery_address">Delivery address <span class="text-danger">*</span></label><textarea class="form-control" id="delivery_address" name="delivery_address" rows="3" maxlength="2000" autocomplete="street-address" placeholder="Full address including postcode and state">{{ old('delivery_address') }}</textarea></div>
                        <div class="mb-3"><label class="form-label" for="delivery_date">Preferred delivery date <span class="text-danger">*</span></label><input class="form-control" type="date" id="delivery_date" name="delivery_date" value="{{ old('delivery_date') }}"></div>
                    </div>
                    <div class="mb-3"><label class="form-label" for="return_date">Return date <span class="text-danger">*</span></label><input class="form-control" type="date" id="return_date" name="return_date" value="{{ old('return_date') }}" required><div class="form-text">Choose a return date on or after your pickup or delivery date.</div></div>
                    <div><label class="form-label" for="notes">Additional notes <span class="text-muted">(optional)</span></label><textarea class="form-control" id="notes" name="notes" rows="3" maxlength="5000" placeholder="Urgency, special requirements or anything else for our team">{{ old('notes') }}</textarea></div>
                </div>
            </section>
            <div class="d-grid mb-4"><button class="btn btn-primary btn-lg" type="submit">Review sample request</button></div>
        </div>

        <section id="request-review" class="card request-card" hidden>
            <div class="card-header"><span class="section-kicker">05 · Review and submit</span><strong>Check your request</strong></div>
            <div class="card-body"><p class="request-help">Make sure these details are correct before sending your request to Victo.</p><div id="review-summary"></div>
                <div class="d-flex flex-column flex-sm-row gap-2 mt-4"><button class="btn btn-outline-secondary flex-fill" type="button" id="edit-request">Back and edit</button><button class="btn btn-primary flex-fill" type="submit" id="confirm-submit">Submit sample request</button></div>
            </div>
        </section>
    </form>
</div>
<script>
(() => {
    const form = document.getElementById('sample-request-form');
    const fields = document.getElementById('request-fields');
    const review = document.getElementById('request-review');
    const method = document.getElementById('collection_method');
    const office = document.getElementById('office-fields');
    const lalamove = document.getElementById('lalamove-fields');
    const pickup = document.getElementById('pickup_date');
    const delivery = document.getElementById('delivery_date');
    const returnDate = document.getElementById('return_date');
    let reviewing = false;
    const showFields = (container, enabled) => {
        container.hidden = !enabled;
        container.querySelectorAll('input,textarea').forEach(input => { input.disabled = !enabled; input.required = enabled && input.id !== 'delivery_address' ? true : enabled; });
    };
    const updateMethod = () => {
        const isOffice = method.value === 'office';
        showFields(office, isOffice);
        showFields(lalamove, method.value === 'lalamove');
        returnDate.min = isOffice ? pickup.value : delivery.value;
    };
    method.addEventListener('change', updateMethod);
    pickup.addEventListener('change', updateMethod);
    delivery.addEventListener('change', updateMethod);
    updateMethod();

    let nextIndex = {{ count(old('items', [['item_type' => 'shirt', 'quantity' => 1]])) }};
    const list = document.getElementById('sample-items-list');
    document.getElementById('add-sample-item').addEventListener('click', () => {
        const row = document.createElement('div');
        row.className = 'sample-row';
        row.innerHTML = `<div class="row g-3"><div class="col-12 col-sm-5"><label class="form-label" for="item-type-${nextIndex}">Item type <span class="text-danger">*</span></label><select class="form-select" id="item-type-${nextIndex}" name="items[${nextIndex}][item_type]" required><option value="shirt">Shirt</option><option value="short">Short</option></select></div><div class="col-12 col-sm-3"><label class="form-label">Quantity <span class="text-danger">*</span></label><input class="form-control" type="number" min="1" max="1000" name="items[${nextIndex}][quantity]" value="1" required></div><div class="col-12 col-sm-4"><label class="form-label">Fabric</label><input class="form-control" name="items[${nextIndex}][fabric]" maxlength="255" placeholder="e.g. Microfiber"></div><div class="col-12"><label class="form-label">Description / requirements</label><textarea class="form-control" name="items[${nextIndex}][description]" rows="2" maxlength="5000" placeholder="Colour, style or other details"></textarea></div><div class="col-12 text-end"><button class="btn btn-link text-danger p-0 remove-sample-item" type="button">Remove item</button></div></div>`;
        list.appendChild(row); nextIndex++;
    });
    list.addEventListener('click', event => {
        if (event.target.closest('.remove-sample-item') && list.querySelectorAll('.sample-row').length > 1) event.target.closest('.sample-row').remove();
    });
    const val = id => document.getElementById(id).value.trim();
    const escapeHtml = value => { const node = document.createElement('span'); node.textContent = value || '—'; return node.innerHTML; };
    const displayDate = value => value ? new Date(value + 'T00:00:00').toLocaleDateString('en-MY',{day:'2-digit',month:'short',year:'numeric'}) : '—';
    const line = (label, value) => `<div class="review-line"><span class="text-muted">${label}</span><span class="review-value">${escapeHtml(value)}</span></div>`;
    form.addEventListener('submit', event => {
        if (reviewing) return;
        event.preventDefault();
        if (!form.reportValidity()) return;
        const parts = [line('Full name',val('full_name')),line('Phone',val('phone')),line('Company',val('company')),line('Email',val('email')),line('Collection',method.options[method.selectedIndex].text)];
        if(method.value==='office') parts.push(line('Pickup date',displayDate(val('pickup_date')))); else { parts.push(line('Delivery date',displayDate(val('delivery_date')))); parts.push(line('Delivery address',val('delivery_address'))); }
        parts.push(line('Return date',displayDate(val('return_date'))));
        document.querySelectorAll('.sample-row').forEach((row,index)=>{
            const get = suffix => row.querySelector(`[name$="[${suffix}]"]`).value.trim();
            parts.push(line(`Sample ${index+1}`,`${get('item_type').replace(/^./, c=>c.toUpperCase())} · Qty ${get('quantity')}${get('fabric') ? ' · '+get('fabric') : ''}${get('description') ? ' · '+get('description') : ''}`));
        });
        parts.push(line('Additional notes',val('notes')));
        document.getElementById('review-summary').innerHTML=parts.join('');
        fields.hidden=true; review.hidden=false; reviewing=true; review.scrollIntoView({behavior:'smooth',block:'start'});
    });
    document.getElementById('edit-request').addEventListener('click',()=>{review.hidden=true;fields.hidden=false;reviewing=false;document.getElementById('full_name').focus();});
})();
</script>
@endsection

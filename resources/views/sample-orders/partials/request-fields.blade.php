<style>
    .sample-request-fields{max-width:820px;margin:0 auto}.sample-request-fields .request-card{border:1px solid #e5eaf2;border-radius:16px;box-shadow:0 8px 28px rgba(21,44,79,.05);margin-bottom:16px}.sample-request-fields .request-card>.card-header{background:#fff;border:0;padding:20px 20px 0}.sample-request-fields .request-card>.card-header:not(.d-flex){display:block}.sample-request-fields .request-card>.card-body{padding:18px 20px 22px}.sample-request-fields .section-kicker{display:block;font-size:.76rem;text-transform:uppercase;letter-spacing:.09em;color:#7c8ba2;font-weight:700}.sample-request-fields .form-control,.sample-request-fields .form-select{min-height:46px;border-radius:10px}.sample-request-fields textarea.form-control{min-height:88px}.sample-request-fields .sample-row{background:#f7f9fc;border:1px solid #e8edf4;border-radius:12px;padding:14px;margin-bottom:12px}.sample-request-fields .btn{border-radius:10px;font-weight:600}.sample-request-fields .form-text{color:#6e7d92}.sample-request-fields [hidden]{display:none!important}
    @media(max-width:575px){.sample-request-fields .request-card>.card-header{padding:18px 16px 0}.sample-request-fields .request-card>.card-body{padding:16px}.sample-request-fields .sample-row{padding:12px}}
</style>
<div class="sample-request-fields">
    <section class="card request-card">
        <div class="card-header"><span class="section-kicker">01 · Customer</span><strong>Customer information</strong></div>
        <div class="card-body">
            <div class="mb-3"><label class="form-label" for="full_name">Full name <span class="text-danger">*</span></label><input class="form-control @error('full_name') is-invalid @enderror" id="full_name" name="full_name" value="{{ old('full_name', old('customer_name')) }}" autocomplete="name" maxlength="255" required>@error('full_name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="row g-3">
                <div class="col-sm-6"><label class="form-label" for="phone">Phone number <span class="text-danger">*</span></label><input class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" type="tel" value="{{ old('phone') }}" autocomplete="tel" inputmode="tel" placeholder="012 345 6789" maxlength="30" required>@error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-sm-6"><label class="form-label" for="company">Company <span class="text-muted">(optional)</span></label><input class="form-control" id="company" name="company" value="{{ old('company') }}" autocomplete="organization" maxlength="255"></div>
            </div>
            <div class="mt-3"><label class="form-label" for="email">Email <span class="text-muted">(optional)</span></label><input class="form-control" id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" maxlength="255"></div>
        </div>
    </section>

    @php($requestItems = old('items', [['item_type' => 'shirt', 'sample_name' => '', 'quantity' => 1, 'fabric' => '', 'description' => '']]))
    <section class="card request-card">
        <div class="card-header d-flex justify-content-between align-items-center gap-3"><div><span class="section-kicker">02 · What you need</span><strong>Sample items</strong></div><button type="button" class="btn btn-outline-primary btn-sm" id="add-sample-item">+ Add another sample</button></div>
        <div class="card-body"><p class="form-text mb-3">Add at least one item. Choose Others to describe a sample outside the standard catalogue.</p>
            <div id="sample-items-list">
                @foreach($requestItems as $index => $item)
                    <div class="sample-row" data-sample-row>
                        <div class="row g-3">
                            <div class="col-12 col-sm-5"><label class="form-label" for="item-type-{{ $index }}">Item type <span class="text-danger">*</span></label><select class="form-select item-type @error("items.$index.item_type") is-invalid @enderror" id="item-type-{{ $index }}" name="items[{{ $index }}][item_type]" required><option value="shirt" @selected(($item['item_type'] ?? '') === 'shirt')>Shirt</option><option value="short" @selected(($item['item_type'] ?? '') === 'short')>Short</option><option value="others" @selected(($item['item_type'] ?? '') === 'others')>Others</option></select></div>
                            <div class="col-12 col-sm-3"><label class="form-label">Quantity <span class="text-danger">*</span></label><input class="form-control" type="number" min="1" max="1000" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required></div>
                            <div class="col-12 col-sm-4 sample-name-field" @if(($item['item_type'] ?? '') !== 'others') hidden @endif><label class="form-label">Sample name <span class="text-danger">*</span></label><input class="form-control sample-name" name="items[{{ $index }}][sample_name]" value="{{ $item['sample_name'] ?? '' }}" maxlength="255" placeholder="Please specify the sample you are borrowing" @if(($item['item_type'] ?? '') !== 'others') disabled @else required @endif>@error("items.$index.sample_name")<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
                            <div class="col-12 col-sm-4 fabric-field" @if(($item['item_type'] ?? '') === 'others') hidden @endif><label class="form-label">Fabric <span class="text-muted">(optional)</span></label><input class="form-control fabric-input" name="items[{{ $index }}][fabric]" value="{{ $item['fabric'] ?? '' }}" maxlength="255" placeholder="e.g. Microfiber" @if(($item['item_type'] ?? '') === 'others') disabled @endif></div>
                            <div class="col-12"><label class="form-label">Description / requirements <span class="text-muted">(optional)</span></label><textarea class="form-control" name="items[{{ $index }}][description]" rows="2" maxlength="5000" placeholder="Colour, style or other details">{{ $item['description'] ?? '' }}</textarea></div>
                            <div class="col-12 text-end"><button class="btn btn-link text-danger p-0 remove-sample-item" type="button">Remove item</button></div>
                        </div>
                    </div>
                @endforeach
            </div>
            @error('items')<div class="text-danger small">{{ $message }}</div>@enderror
        </div>
    </section>

    <section class="card request-card">
        <div class="card-header"><span class="section-kicker">03 · Collection</span><strong>Collection method</strong></div>
        <div class="card-body"><label class="form-label" for="collection_method">Choose one <span class="text-danger">*</span></label><select class="form-select @error('collection_method') is-invalid @enderror" id="collection_method" name="collection_method" required><option value="">Select collection method</option><option value="office" @selected(old('collection_method') === 'office')>Office pickup</option><option value="lalamove" @selected(old('collection_method') === 'lalamove')>Lalamove delivery</option></select><div class="form-text">Lalamove delivery is arranged separately with the Victo team.</div>@error('collection_method')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    </section>

    <section class="card request-card">
        <div class="card-header"><span class="section-kicker">04 · Dates and details</span><strong>Plan your sample</strong></div>
        <div class="card-body">
            <div id="office-fields" hidden><div class="mb-3"><label class="form-label" for="pickup_date">Pickup date <span class="text-danger">*</span></label><input class="form-control @error('pickup_date') is-invalid @enderror" type="date" id="pickup_date" name="pickup_date" value="{{ old('pickup_date') }}"><div class="form-text">Choose the date you plan to collect the sample from Victo.</div>@error('pickup_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
            <div id="lalamove-fields" hidden><div class="mb-3"><label class="form-label" for="delivery_address">Delivery address <span class="text-danger">*</span></label><textarea class="form-control @error('delivery_address') is-invalid @enderror" id="delivery_address" name="delivery_address" rows="3" maxlength="2000" autocomplete="street-address" placeholder="Full address including postcode and state">{{ old('delivery_address') }}</textarea>@error('delivery_address')<div class="invalid-feedback">{{ $message }}</div>@enderror</div><div class="mb-3"><label class="form-label" for="delivery_date">Preferred delivery date <span class="text-danger">*</span></label><input class="form-control @error('delivery_date') is-invalid @enderror" type="date" id="delivery_date" name="delivery_date" value="{{ old('delivery_date') }}">@error('delivery_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
            <div class="mb-3"><label class="form-label" for="return_date">Return date <span class="text-danger">*</span></label><input class="form-control @error('return_date') is-invalid @enderror" type="date" id="return_date" name="return_date" value="{{ old('return_date') }}" required><div class="form-text">Choose a date on or after pickup or delivery.</div>@error('return_date')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div><label class="form-label" for="notes">Additional notes <span class="text-muted">(optional)</span></label><textarea class="form-control" id="notes" name="notes" rows="3" maxlength="5000" placeholder="Urgency, special requirements or anything else for our team">{{ old('notes') }}</textarea></div>
        </div>
    </section>
</div>
<script>
(() => {
    const root = document.currentScript.previousElementSibling;
    const form = root.closest('form');
    const method = form.querySelector('#collection_method');
    const office = form.querySelector('#office-fields');
    const lalamove = form.querySelector('#lalamove-fields');
    const pickup = form.querySelector('#pickup_date');
    const delivery = form.querySelector('#delivery_date');
    const returnDate = form.querySelector('#return_date');
    const toggle = (element, enabled) => { element.hidden = !enabled; element.querySelectorAll('input,textarea').forEach(field => { field.disabled = !enabled; field.required = enabled; }); };
    const syncMethod = () => { const isOffice = method.value === 'office'; toggle(office, isOffice); toggle(lalamove, method.value === 'lalamove'); returnDate.min = isOffice ? pickup.value : delivery.value; };
    method.addEventListener('change', syncMethod); pickup.addEventListener('change', syncMethod); delivery.addEventListener('change', syncMethod); syncMethod();
    let nextIndex = {{ count($requestItems) }};
    const list = form.querySelector('#sample-items-list');
    form.querySelector('#add-sample-item').addEventListener('click', () => {
        const row = document.createElement('div'); row.className = 'sample-row'; row.dataset.sampleRow = '';
        row.innerHTML = `<div class="row g-3"><div class="col-12 col-sm-5"><label class="form-label" for="item-type-${nextIndex}">Item type <span class="text-danger">*</span></label><select class="form-select item-type" id="item-type-${nextIndex}" name="items[${nextIndex}][item_type]" required><option value="shirt">Shirt</option><option value="short">Short</option><option value="others">Others</option></select></div><div class="col-12 col-sm-3"><label class="form-label">Quantity <span class="text-danger">*</span></label><input class="form-control" type="number" min="1" max="1000" name="items[${nextIndex}][quantity]" value="1" required></div><div class="col-12 col-sm-4 sample-name-field" hidden><label class="form-label">Sample name <span class="text-danger">*</span></label><input class="form-control sample-name" name="items[${nextIndex}][sample_name]" maxlength="255" placeholder="Please specify the sample you are borrowing" disabled></div><div class="col-12 col-sm-4 fabric-field"><label class="form-label">Fabric <span class="text-muted">(optional)</span></label><input class="form-control fabric-input" name="items[${nextIndex}][fabric]" maxlength="255" placeholder="e.g. Microfiber"></div><div class="col-12"><label class="form-label">Description / requirements <span class="text-muted">(optional)</span></label><textarea class="form-control" name="items[${nextIndex}][description]" rows="2" maxlength="5000" placeholder="Colour, style or other details"></textarea></div><div class="col-12 text-end"><button class="btn btn-link text-danger p-0 remove-sample-item" type="button">Remove item</button></div></div>`;
        list.appendChild(row); nextIndex++; syncItem(row);
    });
    const syncItem = row => { const isOther = row.querySelector('.item-type').value === 'others'; const nameWrap = row.querySelector('.sample-name-field'); const name = row.querySelector('.sample-name'); const fabricWrap = row.querySelector('.fabric-field'); const fabric = row.querySelector('.fabric-input'); nameWrap.hidden = !isOther; name.disabled = !isOther; name.required = isOther; fabricWrap.hidden = isOther; fabric.disabled = isOther; };
    list.querySelectorAll('[data-sample-row]').forEach(row => { syncItem(row); row.querySelector('.item-type').addEventListener('change', () => syncItem(row)); });
    list.addEventListener('change', event => { if (event.target.matches('.item-type')) syncItem(event.target.closest('[data-sample-row]')); });
    list.addEventListener('click', event => { if (event.target.closest('.remove-sample-item') && list.querySelectorAll('[data-sample-row]').length > 1) event.target.closest('[data-sample-row]').remove(); });
})();
</script>

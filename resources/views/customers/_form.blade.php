<div class="mb-3">
    <label class="form-label">Customer Name *</label>
    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $customer->customer_name ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">Phone *</label>
    <input type="text" name="phone" class="form-control" value="{{ old('phone', $customer->phone ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">Email</label>
    <input type="email" name="email" class="form-control" value="{{ old('email', $customer->email ?? '') }}">
</div>

<div class="mb-3">
    <label class="form-label">Company</label>
    <input type="text" name="company" class="form-control" value="{{ old('company') }}">
</div>

<div class="mb-3">
    <label class="form-label">Address</label>
    <textarea name="address" class="form-control">{{ old('address', $customer->address ?? '') }}</textarea>
</div>

<div class="row">

    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">State</label>
            <input type="text" name="state" class="form-control" value="{{ old('state') }}">
        </div>
    </div>

    <div class="col-md-6">
        <div class="mb-3">
            <label class="form-label">Postcode</label>
            <input type="text" name="postcode" class="form-control" value="{{ old('postcode') }}">
        </div>
    </div>

</div>

<div class="mb-3">
    <label class="form-label">Remarks</label>
    <textarea name="remarks" class="form-control">{{ old('remarks', $customer->remarks ?? '') }}</textarea>
</div>
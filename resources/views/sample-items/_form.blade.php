<div class="card shadow-sm">
    <div class="card-header bg-white"><strong>Item information</strong></div>
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger"><strong>Please check the highlighted fields.</strong><ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form action="{{ $action }}" method="POST">
            @csrf
            @if($httpMethod !== 'POST') @method($httpMethod) @endif
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="item_type" class="form-label">Item type <span class="text-danger">*</span></label>
                    <select name="item_type" id="item_type" class="form-select @error('item_type') is-invalid @enderror" required>
                        <option value="">Select item type</option>
                        <option value="shirt" @selected(old('item_type', $sampleItem->item_type ?? '') === 'shirt')>Shirt</option>
                        <option value="short" @selected(old('item_type', $sampleItem->item_type ?? '') === 'short')>Short</option>
                        <option value="others" @selected(old('item_type', $sampleItem->item_type ?? '') === 'others')>Others</option>
                    </select>
                    @error('item_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6" id="sample-name-group" @if(old('item_type', $sampleItem->item_type ?? '') !== 'others') hidden @endif>
                    <label for="sample_name" class="form-label">Sample name <span class="text-danger">*</span></label>
                    <input type="text" name="sample_name" id="sample_name" class="form-control @error('sample_name') is-invalid @enderror" value="{{ old('sample_name', $sampleItem->sample_name ?? '') }}" maxlength="255" placeholder="Please specify the sample you are borrowing" @if(old('item_type', $sampleItem->item_type ?? '') === 'others') required @else disabled @endif>
                    @error('sample_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label for="quantity" class="form-label">Quantity <span class="text-danger">*</span></label>
                    <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', $sampleItem->quantity ?? 1) }}" min="1" required>
                    @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <div id="fabric-group" @if(old('item_type', $sampleItem->item_type ?? '') === 'others') hidden @endif><label for="fabric" class="form-label">Fabric <span class="text-muted">(optional)</span></label>
                    <input type="text" name="fabric" id="fabric" class="form-control @error('fabric') is-invalid @enderror" value="{{ old('fabric', $sampleItem->fabric ?? '') }}" placeholder="e.g. Cotton, Microfiber, Polyester" @if(old('item_type', $sampleItem->item_type ?? '') === 'others') disabled @endif>
                    @error('fabric')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                </div>
                <div class="col-md-6">
                    <label for="sample_id" class="form-label">Physical sample <span class="text-muted">(optional)</span></label>
                    <select name="sample_id" id="sample_id" class="form-select @error('sample_id') is-invalid @enderror">
                        <option value="">No physical sample linked</option>
                        @foreach($samples as $sample)
                            <option value="{{ $sample->id }}" @selected((string) old('sample_id', $sampleItem->sample_id ?? '') === (string) $sample->id)>{{ $sample->sample_code }} · {{ ucfirst($sample->item_type) }}{{ $sample->fabric ? ' · '.$sample->fabric : '' }}</option>
                        @endforeach
                    </select>
                    @error('sample_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label for="description" class="form-label">Description</label>
                    <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4" maxlength="5000" placeholder="Describe the sample item...">{{ old('description', $sampleItem->description ?? '') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="{{ route('sample-orders.show', $sampleOrder) }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">{{ $submitLabel }}</button>
            </div>
        </form>
    </div>
</div>
<script>
(() => { const type=document.getElementById('item_type'); const nameGroup=document.getElementById('sample-name-group'); const name=document.getElementById('sample_name'); const fabricGroup=document.getElementById('fabric-group'); const fabric=document.getElementById('fabric'); const sync=()=>{const other=type.value==='others';nameGroup.hidden=!other;name.required=other;name.disabled=!other;fabricGroup.hidden=other;fabric.disabled=other;};type.addEventListener('change',sync);sync(); })();
</script>

<?php

namespace App\Http\Controllers;

use App\Models\SampleItem;
use App\Models\SampleOrder;
use App\Models\Sample;
use Illuminate\Http\Request;

class SampleItemController extends Controller
{
    public function create(SampleOrder $sampleOrder)
    {
        $samples = Sample::orderBy('sample_code')->get();

        return view('sample-items.create', compact('sampleOrder', 'samples'));
    }

    public function store(Request $request, SampleOrder $sampleOrder)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:shirt,short,others',
            'sample_name' => 'required_if:item_type,others|nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'fabric' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'sample_id' => 'nullable|integer|exists:samples,id',
        ]);

        if ($validated['item_type'] !== 'others') $validated['sample_name'] = null;
        $validated['sample_order_id'] = $sampleOrder->id;

        SampleItem::create($validated);

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample item added successfully.');
    }

    public function edit(SampleItem $sampleItem)
    {
        $samples = Sample::orderBy('sample_code')->get();

        return view('sample-items.edit', compact('sampleItem', 'samples'));
    }

    public function update(Request $request, SampleItem $sampleItem)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:shirt,short,others',
            'sample_name' => 'required_if:item_type,others|nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'fabric' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'sample_id' => 'nullable|integer|exists:samples,id',
        ]);

        if ($validated['item_type'] !== 'others') $validated['sample_name'] = null;
        $sampleItem->update($validated);

        return redirect()
            ->route('sample-orders.show', $sampleItem->sampleOrder)
            ->with('success', 'Sample item updated successfully.');
    }

    public function destroy(SampleItem $sampleItem)
    {
        $sampleOrder = $sampleItem->sampleOrder;
        $sampleItem->delete();

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample item removed successfully.');
    }
}

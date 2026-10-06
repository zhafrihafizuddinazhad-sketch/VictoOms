<?php

namespace App\Http\Controllers;

use App\Models\SampleItem;
use App\Models\SampleOrder;
use App\Models\Sample;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SampleItemController extends Controller
{
    public function create(SampleOrder $sampleOrder)
    {
        abort_if($sampleOrder->sampleItemsAreLocked(), 403);

        $samples = Sample::orderBy('sample_code')->get();

        return view('sample-items.create', compact('sampleOrder', 'samples'));
    }

    public function store(Request $request, SampleOrder $sampleOrder)
    {
        abort_if($sampleOrder->sampleItemsAreLocked(), 403);

        $validated = $request->validate([
            'item_type' => 'required|in:shirt,short,others',
            'sample_name' => 'required_if:item_type,others|nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'fabric' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'sample_id' => 'nullable|integer|exists:samples,id',
        ]);

        if ($validated['item_type'] !== 'others') $validated['sample_name'] = null;

        DB::transaction(function () use ($sampleOrder, $validated): void {
            $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleOrder->id);
            abort_if($lockedOrder->sampleItemsAreLocked(), 403);

            $lockedOrder->sampleItems()->create($validated);
        });

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample item added successfully.');
    }

    public function edit(SampleItem $sampleItem)
    {
        abort_if($sampleItem->sampleOrder->sampleItemsAreLocked(), 403);

        $samples = Sample::orderBy('sample_code')->get();

        return view('sample-items.edit', compact('sampleItem', 'samples'));
    }

    public function update(Request $request, SampleItem $sampleItem)
    {
        abort_if($sampleItem->sampleOrder->sampleItemsAreLocked(), 403);

        $validated = $request->validate([
            'item_type' => 'required|in:shirt,short,others',
            'sample_name' => 'required_if:item_type,others|nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'fabric' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
            'sample_id' => 'nullable|integer|exists:samples,id',
        ]);

        if ($validated['item_type'] !== 'others') $validated['sample_name'] = null;

        DB::transaction(function () use ($sampleItem, $validated): void {
            $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleItem->sample_order_id);
            abort_if($lockedOrder->sampleItemsAreLocked(), 403);

            $sampleItem->update($validated);
        });

        return redirect()
            ->route('sample-orders.show', $sampleItem->sampleOrder)
            ->with('success', 'Sample item updated successfully.');
    }

    public function destroy(SampleItem $sampleItem)
    {
        $sampleOrder = $sampleItem->sampleOrder;

        DB::transaction(function () use ($sampleItem): void {
            $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleItem->sample_order_id);
            abort_if($lockedOrder->sampleItemsAreLocked(), 403);

            $sampleItem->delete();
        });

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample item removed successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SampleOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SampleReturnController extends Controller
{
    public function store(Request $request, SampleOrder $sampleOrder)
    {
        $data = $request->validate([
            'returned_at' => 'nullable|date',
            'condition' => 'required|in:good,damaged,lost,other',
            'deposit_action' => 'nullable|in:refund,partial_refund,forfeit,deduct,hold',
            'damage_description' => 'nullable|string|max:5000|required_if:condition,damaged,lost,other',
            'notes' => 'nullable|string|max:5000',
        ]);

        DB::transaction(function () use ($request, $sampleOrder, $data): void {
            $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleOrder->id);
            $existingReturn = $lockedOrder->sampleReturn;

            if (! $existingReturn) {
                if ($lockedOrder->status !== 'return_pending') {
                    throw ValidationException::withMessages([
                        'condition' => 'Mark the sample as return pending before recording its return.',
                    ]);
                }
            }

            $lockedOrder->sampleReturn()->updateOrCreate([], [
                'returned_at' => $data['returned_at'] ?? now(),
                'received_by' => $request->user()->id,
                'condition' => $data['condition'],
                'deposit_action' => $data['deposit_action'] ?? null,
                'damage_description' => $data['damage_description'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            if ($lockedOrder->status !== 'completed') {
                $lockedOrder->update(['status' => 'returned']);
            }
        });

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Sample return and inspection recorded successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SampleOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    private const METHODS = ['cash', 'bank_transfer', 'online'];
    private const STATUSES = ['pending', 'paid', 'failed', 'refunded'];

    public function store(Request $request, SampleOrder $sampleOrder)
    {
        $data = $this->validatedPayment($request);

        DB::transaction(function () use ($request, $sampleOrder, $data): void {
            $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleOrder->id);
            $paid = $data['status'] === 'paid';

            $lockedOrder->payments()->create([
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'paid_at' => $paid ? ($data['paid_at'] ?? now()) : null,
                'confirmed_by' => $paid ? $request->user()->id : null,
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncDepositStatus($lockedOrder);
        });

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Payment recorded successfully.');
    }

    public function update(Request $request, SampleOrder $sampleOrder, Payment $payment)
    {
        abort_unless($payment->sample_order_id === $sampleOrder->id, 404);
        $data = $this->validatedPayment($request);

        DB::transaction(function () use ($request, $sampleOrder, $payment, $data): void {
            $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleOrder->id);
            $lockedPayment = Payment::query()->lockForUpdate()->findOrFail($payment->id);
            $wasPaid = $lockedPayment->status === 'paid';
            $isPaid = $data['status'] === 'paid';

            $lockedPayment->update([
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'status' => $data['status'],
                'paid_at' => $isPaid
                    ? ($data['paid_at'] ?? $lockedPayment->paid_at ?? now())
                    : (($data['status'] === 'refunded' && $wasPaid) ? $lockedPayment->paid_at : null),
                'confirmed_by' => $isPaid
                    ? $request->user()->id
                    : (($data['status'] === 'refunded' && $wasPaid) ? $lockedPayment->confirmed_by : null),
                'notes' => $data['notes'] ?? null,
            ]);

            $this->syncDepositStatus($lockedOrder);
        });

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Payment updated successfully.');
    }

    private function validatedPayment(Request $request): array
    {
        return $request->validate([
            'amount' => 'required|numeric|min:0',
            'payment_method' => ['required', 'in:' . implode(',', self::METHODS)],
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
            'paid_at' => 'nullable|date',
            'notes' => 'nullable|string|max:5000',
        ]);
    }

    private function syncDepositStatus(SampleOrder $sampleOrder): void
    {
        $depositStatus = $sampleOrder->payments()->where('status', 'paid')->exists()
            ? 'paid'
            : ($sampleOrder->payments()->where('status', 'refunded')->exists() ? 'refunded' : 'pending');

        if ($sampleOrder->deposit_status !== $depositStatus) {
            $sampleOrder->update(['deposit_status' => $depositStatus]);
        }

        if ($depositStatus === 'paid' && $sampleOrder->status === 'pending_payment') {
            $nextStatus = $sampleOrder->collection_method === 'office'
                ? 'ready_for_collection'
                : 'pending';

            $sampleOrder->update(['status' => $nextStatus]);

            if ($nextStatus === 'ready_for_collection') {
                $sampleOrder->events()->create([
                    'event_key' => 'prepared_for_pickup',
                    'user_id' => auth()->id(),
                ]);
            }
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\SampleOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class CustomerSamplePhotoController extends Controller
{
    public function show(string $customerToken)
    {
        $sampleOrder = $this->findLalamoveOrder($customerToken);
        if (! $sampleOrder) {
            return response()->view('customer-sample-photos.invalid', [], 404);
        }

        return view('customer-sample-photos.show', compact('sampleOrder', 'customerToken'));
    }

    public function store(Request $request, string $customerToken)
    {
        $sampleOrder = $this->findLalamoveOrder($customerToken);
        if (! $sampleOrder) {
            return response()->view('customer-sample-photos.invalid', [], 404);
        }

        $validated = $request->validate([
            'photo_type' => ['required', Rule::in(['customer_received', 'before_customer_return'])],
            'photos' => 'required|array|min:1|max:5',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validated['photo_type'] === 'customer_received'
            && ! in_array($sampleOrder->status, ['in_transit', 'received'], true)) {
            return back()->withErrors(['photo_type' => 'This upload link is ready after the sample has been sent.']);
        }
        if ($validated['photo_type'] === 'before_customer_return'
            && ! in_array($sampleOrder->status, ['received', 'return_pending'], true)) {
            return back()->withErrors(['photo_type' => 'The return photo can be uploaded after you have received the sample.']);
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use ($validated, $sampleOrder, &$storedPaths): void {
                $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleOrder->id);
                $stageAllowsUpload = $validated['photo_type'] === 'customer_received'
                    ? in_array($lockedOrder->status, ['in_transit', 'received'], true)
                    : in_array($lockedOrder->status, ['received', 'return_pending'], true);
                if (! $stageAllowsUpload) {
                    throw ValidationException::withMessages([
                        'photo_type' => 'This photo update is not available at the current order stage.',
                    ]);
                }

                foreach ($validated['photos'] as $photo) {
                    $path = $photo->store("sample-orders/{$lockedOrder->id}/photos", 'local');
                    if (! $path) {
                        throw new \RuntimeException('The image could not be stored.');
                    }

                    $storedPaths[] = $path;
                    $lockedOrder->photos()->create([
                        'photo_type' => $validated['photo_type'],
                        'uploaded_by_customer' => true,
                        'file_path' => $path,
                        'notes' => $validated['notes'] ?? null,
                    ]);
                }

                if ($validated['photo_type'] === 'customer_received' && $lockedOrder->status === 'in_transit') {
                    $lockedOrder->update(['status' => 'received']);
                } elseif ($validated['photo_type'] === 'before_customer_return') {
                    $lockedOrder->update(['status' => 'return_pending']);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        return redirect()
            ->route('customer-sample-photos.show', $customerToken)
            ->with('success', 'Photo uploaded successfully. Thank you.');
    }

    private function findLalamoveOrder(string $customerToken): ?SampleOrder
    {
        return SampleOrder::query()
            ->where('customer_token', $customerToken)
            ->where('collection_method', 'lalamove')
            ->first();
    }
}

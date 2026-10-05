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

        $hasReceivedPhoto = $sampleOrder->photos()->where('photo_type', 'customer_received')->exists();
        $hasBeforeReturnPhoto = $sampleOrder->photos()
            ->whereIn('photo_type', ['before_return', 'before_customer_return'])
            ->exists();
        $photoPurpose = match (true) {
            $sampleOrder->status === 'in_transit' && ! $hasReceivedPhoto => 'customer_received',
            $sampleOrder->status === 'received' && $hasReceivedPhoto && ! $hasBeforeReturnPhoto => 'before_return',
            default => null,
        };
        $customerMessage = match (true) {
            $photoPurpose === 'customer_received' => 'Please upload a photo showing that you have received the sample.',
            $photoPurpose === 'before_return' => 'Please upload a photo of the sample before returning it.',
            $sampleOrder->status === 'return_pending' => 'No customer upload is required right now. Please contact Victo if you need assistance.',
            in_array($sampleOrder->status, ['returned', 'completed'], true) => 'Victo has received the sample. No further photo is needed.',
            default => 'This photo link will be ready after Victo sends the sample.',
        };

        return view('customer-sample-photos.show', compact('sampleOrder', 'customerToken', 'photoPurpose', 'customerMessage'));
    }

    public function store(Request $request, string $customerToken)
    {
        $sampleOrder = $this->findLalamoveOrder($customerToken);
        if (! $sampleOrder) {
            return response()->view('customer-sample-photos.invalid', [], 404);
        }

        $validated = $request->validate([
            'photo_type' => ['required', Rule::in(['customer_received', 'before_return'])],
            'photos' => 'required|array|min:1|max:5',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:500',
        ]);

        $storedPaths = [];

        try {
            DB::transaction(function () use ($validated, $sampleOrder, &$storedPaths): void {
                $lockedOrder = SampleOrder::query()->lockForUpdate()->findOrFail($sampleOrder->id);
                $hasReceivedPhoto = $lockedOrder->photos()->where('photo_type', 'customer_received')->exists();
                $hasBeforeReturnPhoto = $lockedOrder->photos()
                    ->whereIn('photo_type', ['before_return', 'before_customer_return'])
                    ->exists();
                $stageAllowsUpload = $validated['photo_type'] === 'customer_received'
                    ? $lockedOrder->status === 'in_transit' && ! $hasReceivedPhoto
                    : $lockedOrder->status === 'received' && $hasReceivedPhoto && ! $hasBeforeReturnPhoto;
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
                } elseif ($validated['photo_type'] === 'before_return') {
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

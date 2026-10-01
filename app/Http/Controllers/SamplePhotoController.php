<?php

namespace App\Http\Controllers;

use App\Models\SampleOrder;
use App\Models\SamplePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Throwable;

class SamplePhotoController extends Controller
{
    public function store(Request $request, SampleOrder $sampleOrder)
    {
        $allowedCheckpoints = $sampleOrder->collection_method === 'office'
            ? ['before_handover', 'after_return']
            : ['before_delivery', 'after_return'];

        $validated = $request->validate([
            'photo_type' => ['required', Rule::in($allowedCheckpoints)],
            'photos' => 'required|array|min:1|max:10',
            'photos.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'notes' => 'nullable|string|max:1000',
        ]);

        $checkpoint = $validated['photo_type'];
        $depositSatisfied = (float) $sampleOrder->deposit_amount <= 0 || $sampleOrder->deposit_status === 'paid';
        $checkpointReady = match ($checkpoint) {
            'before_handover' => $sampleOrder->status === 'ready_for_collection' && $depositSatisfied,
            'before_delivery' => $sampleOrder->status === 'pending_payment' && $depositSatisfied,
            'after_return' => $sampleOrder->sampleReturn()->exists(),
            default => false,
        };
        if (! $checkpointReady) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'photo_type' => 'This photo checkpoint is not available at the current order stage.',
            ]);
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use ($validated, $sampleOrder, $request, &$storedPaths): void {
                foreach ($validated['photos'] as $photo) {
                    $path = $photo->store("sample-orders/{$sampleOrder->id}/photos", 'local');
                    if (! $path) {
                        throw new \RuntimeException('The image could not be stored.');
                    }

                    $storedPaths[] = $path;
                    $sampleOrder->photos()->create([
                        'photo_type' => $validated['photo_type'],
                        'uploaded_by_user_id' => $request->user()->id,
                        'uploaded_by_customer' => false,
                        'file_path' => $path,
                        'notes' => $validated['notes'] ?? null,
                    ]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', count($storedPaths) . ' photo' . (count($storedPaths) === 1 ? '' : 's') . ' uploaded successfully.');
    }

    public function show(SampleOrder $sampleOrder, SamplePhoto $samplePhoto)
    {
        abort_unless($samplePhoto->sample_order_id === $sampleOrder->id, 404);

        $disk = Storage::disk('local');
        if (! $disk->exists($samplePhoto->file_path)) {
            // Older evidence may have been stored on the public disk before private delivery was added.
            $disk = Storage::disk('public');
        }
        abort_unless($disk->exists($samplePhoto->file_path), 404);

        return $disk->response(
            $samplePhoto->file_path,
            basename($samplePhoto->file_path),
            ['Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'],
            'inline'
        );
    }

    public function destroy(SampleOrder $sampleOrder, SamplePhoto $samplePhoto)
    {
        abort_unless($samplePhoto->sample_order_id === $sampleOrder->id, 404);

        $path = $samplePhoto->file_path;
        foreach (['local', 'public'] as $diskName) {
            $disk = Storage::disk($diskName);
            if ($disk->exists($path) && ! $disk->delete($path)) {
                return back()->withErrors(['photo' => 'The image could not be removed from storage.']);
            }
        }
        $samplePhoto->delete();

        return redirect()
            ->route('sample-orders.show', $sampleOrder)
            ->with('success', 'Photo deleted successfully.');
    }
}

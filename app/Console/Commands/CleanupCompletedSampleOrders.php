<?php

namespace App\Console\Commands;

use App\Models\SampleOrder;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CleanupCompletedSampleOrders extends Command
{
    protected $signature = 'samples:cleanup-completed';

    protected $description = 'Delete completed sample orders after 30 days and clean up their dependent records.';

    private const RETENTION_DAYS = 30;

    private const CHUNK_SIZE = 100;

    public function handle(): int
    {
        $cutoff = Carbon::now()->subDays(self::RETENTION_DAYS);
        $deletedCount = 0;
        $failedCount = 0;

        SampleOrder::query()
            ->where('status', 'completed')
            ->whereNotNull('completed_at')
            ->where('completed_at', '<=', $cutoff)
            ->orderBy('id')
            ->chunkById(self::CHUNK_SIZE, function ($orders) use ($cutoff, &$deletedCount, &$failedCount): void {
                foreach ($orders as $order) {
                    $photoPaths = [];
                    $wasDeleted = false;

                    try {
                        DB::transaction(function () use ($order, $cutoff, &$photoPaths, &$wasDeleted): void {
                            $lockedOrder = SampleOrder::query()
                                ->lockForUpdate()
                                ->find($order->id);

                            if (! $lockedOrder
                                || $lockedOrder->status !== 'completed'
                                || ! $lockedOrder->completed_at
                                || $lockedOrder->completed_at->gt($cutoff)) {
                                return;
                            }

                            $photoPaths = $lockedOrder->photos()
                                ->pluck('file_path')
                                ->filter()
                                ->unique()
                                ->values()
                                ->all();

                            if (! $lockedOrder->delete()) {
                                throw new \RuntimeException('The completed sample order could not be deleted.');
                            }

                            $wasDeleted = true;
                        });
                    } catch (Throwable $exception) {
                        $failedCount++;
                        Log::error('Sample Management cleanup could not delete an eligible order.', [
                            'sample_order_id' => $order->id,
                            'exception' => $exception,
                        ]);
                        continue;
                    }

                    if (! $wasDeleted) {
                        continue;
                    }

                    $deletedCount++;
                    foreach ($photoPaths as $path) {
                        $expectedPrefix = "sample-orders/{$order->id}/photos/";
                        if (! str_starts_with(ltrim($path, '/'), $expectedPrefix)) {
                            continue;
                        }

                        foreach (['local', 'public'] as $diskName) {
                            try {
                                $disk = Storage::disk($diskName);
                                if ($disk->exists($path) && ! $disk->delete($path)) {
                                    throw new \RuntimeException('Stored sample photo could not be removed.');
                                }
                            } catch (Throwable $exception) {
                                $failedCount++;
                                Log::error('Sample Management cleanup could not remove a dependent photo file.', [
                                    'sample_order_id' => $order->id,
                                    'disk' => $diskName,
                                    'exception' => $exception,
                                ]);
                            }
                        }
                    }
                }
            });

        Log::info('Sample Management cleanup completed.', [
            'deleted' => $deletedCount,
            'failed' => $failedCount,
            'cutoff' => $cutoff->toDateTimeString(),
        ]);

        if ($deletedCount === 0 && $failedCount === 0) {
            $this->info('No completed sample orders are eligible for deletion.');

            return self::SUCCESS;
        }

        $this->info("Deleted {$deletedCount} completed sample orders older than 30 days.");
        if ($failedCount > 0) {
            $this->error("{$failedCount} cleanup operation(s) failed. See the Laravel log for details.");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}

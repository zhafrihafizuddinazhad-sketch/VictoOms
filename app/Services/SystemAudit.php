<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;

class SystemAudit
{
    public function record(string $action, string $description, ?User $actor = null): void
    {
        ActivityLog::create([
            'order_id' => null,
            'user_id' => $actor?->getKey(),
            'action' => $action,
            'description' => $description,
        ]);
    }
}

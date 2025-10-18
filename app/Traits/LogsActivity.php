<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait LogsActivity
{
    /**
     * Log activity for the authenticated user
     */
    protected function logActivity(string $module, string $remark): void
    {
        try {
            if (auth()->check()) {
                ActivityLog::create([
                    'account' => auth()->id(),
                    'module' => $module,
                    'remark' => $remark
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail to prevent breaking the main functionality
            logger()->error('Failed to log activity: ' . $e->getMessage());
        }
    }
}

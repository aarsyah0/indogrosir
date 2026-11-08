<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class AuditService
{
    public function log(
        ?User $actor,
        string $action,
        Model $auditable,
        ?array $before = null,
        ?array $after = null,
        ?string $reason = null,
    ): AuditLog {
        return AuditLog::query()->create([
            'actor_id' => $actor?->id,
            'action' => $action,
            'auditable_type' => $auditable->getMorphClass(),
            'auditable_id' => $auditable->getKey(),
            'before_json' => $before,
            'after_json' => $after,
            'reason' => $reason,
            'ip_address' => request()?->ip(),
            'created_at' => now(),
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\WorkflowAlert;
use Illuminate\Support\Collection;

class NotificationService
{
    public function notifyUsers(iterable $users, string $title, string $body, ?string $url = null): void
    {
        Collection::make($users)
            ->unique('id')
            ->each(fn (User $user) => $user->notify(new WorkflowAlert($title, $body, $url)));
    }

    public function notifyRole(string $roleSlug, string $title, string $body, ?string $url = null): void
    {
        $users = User::query()
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->where('slug', $roleSlug))
            ->get();

        $this->notifyUsers($users, $title, $body, $url);
    }
}

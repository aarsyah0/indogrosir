<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'division_id', 'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function division(): BelongsTo
    {
        return $this->belongsTo(Division::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_roles');
    }

    public function managedDivisions(): BelongsToMany
    {
        return $this->belongsToMany(Division::class, 'division_managers')
            ->wherePivotNull('ended_at');
    }

    public function requisitions(): HasMany
    {
        return $this->hasMany(Requisition::class, 'requester_id');
    }

    public function hasRole(string $slug): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains('slug', $slug);
    }

    public function hasPermission(string $slug): bool
    {
        $this->loadMissing('roles.permissions');

        return $this->roles
            ->flatMap->permissions
            ->contains('slug', $slug);
    }

    public function managesDivision(?int $divisionId): bool
    {
        if (! $divisionId) {
            return false;
        }

        return $this->managedDivisions()->where('divisions.id', $divisionId)->exists();
    }

    public function permissionSlugs(): array
    {
        $this->loadMissing('roles.permissions');

        return $this->roles->flatMap->permissions->pluck('slug')->unique()->values()->all();
    }

    public function primaryRoleLabel(): string
    {
        $this->loadMissing('roles');

        return $this->roles->pluck('name')->join(', ') ?: 'Pengguna';
    }
}

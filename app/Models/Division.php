<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Division extends Model
{
    protected $fillable = ['code', 'name', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function managerAssignments(): HasMany
    {
        return $this->hasMany(DivisionManager::class);
    }

    public function activeManagers()
    {
        return $this->belongsToMany(User::class, 'division_managers')
            ->wherePivotNull('ended_at');
    }
}

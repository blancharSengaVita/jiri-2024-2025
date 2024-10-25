<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Contact extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'contact';

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'email',
        'phone',
    ];

    public function user(): BelongsTo
    {
        return $this
            ->belongsTo(User::class);
    }

    public function jiris(): BelongsToMany
    {
        return $this
            ->belongsToMany(Jiri::class, 'attendances', 'contact_id', 'jiri_id')
            ->withPivot(['role', 'token']);
    }

    public function projects(): BelongsToMany
    {
        return $this
            ->belongsToMany(Project::class, 'implementations', 'contact_id', 'project_id')
            ->withPivot(['urls', 'scores', 'tasks']);
    }

    public function implementations(): HasMany
    {
        return $this
            ->HasMany(Implementation::class);
    }

    public function attendances(): HasMany
    {
        return $this
            ->HasMany(Attendance::class);
    }
}

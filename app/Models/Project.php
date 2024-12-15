<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'links',
        'tasks',
        'description',
    ];

    public function user(): BelongsTo
    {
        return $this
            ->belongsTo(User::class);
    }

    public function duties(): HasMany
    {
        return $this
            ->hasMany(Duties::class);
    }
}

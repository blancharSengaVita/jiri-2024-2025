<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}

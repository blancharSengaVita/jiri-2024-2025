<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Duties extends Model
{
    use HasFactory;

    protected $fillable = [
        'weighting',
    ];

    public function jiri(): BelongsTo
    {
        return $this
            ->belongsTo(Jiri::class);
    }

    public function project(): BelongsTo
    {
        return $this
            ->belongsTo(Project::class);
    }
}

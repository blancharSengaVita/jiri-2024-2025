<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Implementation extends Model
{
    use HasFactory;

    public function author(): BelongsTo
    {
        return $this
            ->belongsTo(\App\Models\Contact::class);
    }

    public function jiri(): BelongsTo
    {
        return $this
            ->belongsTo(\App\Models\Jiri::class);
    }

    public function project(): BelongsTo
    {
        return $this
            ->belongsTo(\App\Models\Project::class);
    }

    public function getScoresAttribute($value)
    {
        return json_decode($value);
    }

    public function getUrlsAttribute($value)
    {
        return json_decode($value);
    }

    public function getTasksAttribute($value)
    {
        return json_decode($value);
    }
}

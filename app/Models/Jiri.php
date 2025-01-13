<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jiri extends Model
{
    /** @use HasFactory<\Database\Factories\JiriFactory> */
    use HasFactory;


    protected $fillable = [
        'name',
        'user_id',
        'starting_at',
        'duration',
    ];

    protected $hidden = ['errors'];

    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_FINISHED = 'finished';
    public const STATUS_ON_PAUSE = 'on_pause';

    public static function statusLabels(): array
    {
        return [
            self::STATUS_NOT_STARTED => 'Non commencé',
            self::STATUS_IN_PROGRESS => 'En cours',
            self::STATUS_FINISHED => 'Terminé',
            self::STATUS_ON_PAUSE => 'En pause',
        ];
    }

    public function canBePaused(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canBeStopped(): bool
    {
        return in_array($this->status, [self::STATUS_ON_PAUSE, self::STATUS_IN_PROGRESS]);
    }

    public function canBeRelaunched(): bool
    {
        return $this->status === self::STATUS_ON_PAUSE;
    }

    public function user(): BelongsTo
    {
        return $this
            ->belongsTo(User::class);
    }

    public function implementations(): HasMany
    {
        return $this
            ->hasMany(Implementation::class);
    }

    public function projects(): BelongsToMany
    {
        return $this
            ->belongsToMany(Project::class, 'implementations', 'jiri_id', 'project_id');
    }

    public function attendances(): HasMany
    {
        return $this
            ->hasMany(Attendance::class);
    }

    public function duties(): HasMany
    {
        return $this
            ->hasMany(Duties::class);
    }

    public function contacts(): BelongsToMany
    {
        return $this
            ->belongsToMany(Contact::class, 'attendances', 'jiri_id', 'contact_id');
    }

    public function students(): BelongsToMany
    {
        return $this
            ->belongsToMany(Contact::class, 'attendances', 'jiri_id', 'contact_id')
            ->withPivot('role')
            ->wherePivot('role', 'student');
    }

    public function evaluators(): BelongsToMany
    {
        return $this
            ->belongsToMany(Contact::class, 'attendances', 'jiri_id', 'contact_id')
            ->withPivot('role','token')
            ->wherePivot('role', 'evaluator');
    }
}

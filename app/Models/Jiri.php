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

//    protected function casts(): array
//    {
//        return [
//            'starting_at' => 'datetime:'d F Y',
//        ];
//    }

    protected function startingAt(): Attribute
    {
        return Attribute::make(
//            get: fn ($value) => Carbon::parse($value)->translatedFormat('d F Y'),
//            set: fn (string $value) => strtolower($value),
        );
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

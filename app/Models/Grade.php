<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grade extends Model
{
    /** @use HasFactory<\Database\Factories\CotationFactory> */
    use HasFactory;

    protected $fillable = [
        'jiri_id',
        'duty_id',
        'student_id',
        'evaluator_id',
        'user_id',
        'grade',
        'comment',
    ];


    public function duty(): BelongsTo
    {
        return $this
            ->belongsTo(Duties::class);
    }
}

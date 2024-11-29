<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;

class Attendance extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guard = 'attendance';

    protected $fillable = [
        'token'
    ];

    protected $hidden = [
        'token', 'remember_token',
    ];

    protected $table = 'attendances';

    protected $guarded = ['id'];

    public function getAuthPassword()
    {
        dd($this->token);
        return $this->token;
    }

    public function jiri(): BelongsTo
    {
        return $this
            ->belongsTo(Jiri::class);
    }

    public function contact(): BelongsTo
    {
        return $this
            ->belongsTo(Contact::class);
    }
}

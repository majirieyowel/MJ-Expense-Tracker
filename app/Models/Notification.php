<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Notification extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'user_id',
        'updated_at',
        'created_at'
    ];

    public static function deleteAll(): void
    {
        self::whereUserId(Auth::id())->delete();
    }

    public static function getAllForUser(): Collection
    {
        return self::whereUserId(Auth::id())->get();
    }


    public static function createNotification(int $day, int $time): self
    {
        return self::create([
            "user_id" => Auth::id(),
            "day" => $day,
            "time" => $time
        ]);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }
}

<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Support\Facades\Hash;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'email',
        'password',
        'auth_channel'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'id',
        'password',
        'remember_token',
        'updated_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function createUser($data, $verified = false): User
    {
        return self::create([
            'auth_channel' => $data->auth_channel,
            'username' => $data->username,
            'email' => $data->email,
            'password' => Hash::make($data->password),
            'email_verified_at' => $verified ? now() : null
        ]);
    }

    public static function getByWhatsappMsisdn(string $msisdn): ?self
    {
        return self::where('whatsapp_msisdn', $msisdn)->first();
    }


    public static function signInUser(self $user): string
    {
        $token = $user->createToken("SESSION");

        return $token->plainTextToken;
    }

    public static function getByEmail(string $email)
    {
        return self::whereEmail($email)->first();
    }

    public static function withNotification(self $user): array
    {
        $data = [
            'days' => [],
            'time' => ''
        ];

        foreach ($user->notifications as $notification) {
            $data['days'][] = $notification->day;
            $data['time'] = $notification->time;
        }

        return $data;
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function setting()
    {
        return $this->hasOne(Setting::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });

        static::created(function ($user) {
            $user->setting()->create([
                "data" => json_encode(["ENABLED_NOTIFICATIONS" => false])
            ]);
        });
    }
}

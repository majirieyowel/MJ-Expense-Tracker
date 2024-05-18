<?php

namespace App\Models;

use App\Http\Helpers\Utils;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Item extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'id',
        'user_id',
        'updated_at'
    ];

    public function recentPrices(): HasMany
    {
        return $this->hasMany(Expense::class)->orderBy('amount', 'desc')->take(10);
    }


    public static function getId(string $item, int $user_id): ?int
    {
        $existingItem = null;

        if (Utils::isValidUuid($item)) {
            $existingItem = self::whereUuid($item)->first();
        } else {
            $existingItem = self::where('user_id', $user_id)->where('title', $item)->first();
        }

        if ($existingItem) {
            return $existingItem->id;
        }

        // If the item does not exist, create a new one
        return self::create([
            'user_id' => $user_id,
            'title' => Utils::isValidUuid($item) ? null : $item
        ])->id;
    }

    public static function getByUser(int $number): LengthAwarePaginator
    {
        return self::whereUserId(Auth::id())
            ->orderBy("title", "asc")
            ->paginate($number);
    }

    public static function updateMultipleTitles(array $ids, $title)
    {
        self::whereIn('uuid', $ids)->update([
            'title' => $title
        ]);
    }

    public static function search($query)
    {
        return self::where('title', 'like', '%' . $query . '%')
            ->where('user_id', Auth::id())
            // ->select(['uuid', 'title'])
            ->with('recentPrices')
            ->take(4)
            ->get();
    }


    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }
}

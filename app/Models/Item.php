<?php

namespace App\Models;

use App\Http\Helpers\Utils;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Item extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'id',
        'user_id',
        'updated_at'
    ];


    public static function getId(string $item): ?int
    {

        if (Utils::isValidUuid($item)) {

            $existingItem = self::whereUuid($item)->first();

            if (!$existingItem) {

                return null;
            }

            return $existingItem->id;
        } else {

            $item = self::firstOrCreate([
                'user_id' => Auth::id(),
                'title' => $item
            ])->first();

            return $item->id;
        }
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
            ->select(['uuid', 'title'])
            ->take(10)
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

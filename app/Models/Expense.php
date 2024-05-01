<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $hidden = [
        'id',
        'user_id',
        'updated_at'
    ];

    public static function getByUser(int $number): LengthAwarePaginator
    {
        return self::whereUserId(Auth::id())
            ->orderBy("created_at", "desc")
            ->with('item')
            ->paginate($number);
    }

    public static function getByUuid(string $uuid): ?Expense
    {
        return self::where("user_id", Auth::id())
            ->where("uuid", $uuid)
            ->first();
    }


    public static function createExpense($data)
    {
        return self::create([
            "user_id" => Auth::id(),
            "item_id" => $data->item_id,
            "amount" => $data->amount,
            "expense_date" => $data->date
        ]);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public static function deleteExpense(self $expense)
    {
        $expense->delete();
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->uuid = Str::uuid();
        });
    }
}

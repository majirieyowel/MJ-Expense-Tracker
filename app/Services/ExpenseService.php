<?php
namespace App\Services;

use App\Models\Item;
use App\Models\Expense;
use App\Constants\Status;
use Illuminate\Support\Facades\Auth;

class ExpenseService
{

    protected $dateFormat = "Y-m-d";

    protected $userId;

    /**
     * Store an expense
     * 
     * @param string $item 
     * @param float $amount
     * @param string $date
     * 
     * @return array
     */
    public function store(string $item, float $amount, string $date, int $user_id): array
    {

        $itemId = Item::getId($item, $user_id);

        if (!$itemId) {
            return [Status::ERROR, "Unable to save expense at the moment", 500];
        }

        $newExpense = Expense::createExpense((object) [
            "item_id" => $itemId,
            "amount" => $amount,
            "date" => $date,
            "user_id" => $user_id,
        ]);

        return [Status::OK, $newExpense, null];
    }

    /**
     * Get expense summary
     * 
     * @param int $userId
     * @return array
     */
    public function summary($userId): array
    {
        $this->userId = $userId;

        $data['today'] = $this->fetchTodayExpense();
        $data['past_week'] = $this->fetchPastWeekExpense();
        $data['this_month'] = $this->fetchMonthExpense();
        $data['this_year'] = $this->fetchYearExpense();

        return $data;
    }

    private function fetchTodayExpense(): float
    {
        return Expense::where("user_id", $this->userId)
            ->whereDate("expense_date", now()->format($this->dateFormat))
            ->sum("amount");
    }

    private function fetchPastWeekExpense(): float
    {
        return Expense::where("user_id", $this->userId)
            ->whereDate("expense_date", ">=", now()->subWeek()->format($this->dateFormat))
            ->sum("amount");
    }

    private function fetchMonthExpense(): float
    {
        return Expense::where("user_id", $this->userId)
            ->whereDate("expense_date", ">=", now()->firstOfMonth()->format($this->dateFormat))
            ->sum("amount");
    }

    private function fetchYearExpense(): float
    {
        return Expense::where("user_id", $this->userId)
            ->whereDate("expense_date", ">=", now()->firstOfYear()->format($this->dateFormat))
            ->sum("amount");
    }
}

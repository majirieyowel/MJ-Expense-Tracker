<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QueryController extends Controller
{
    protected $dateFormat = "Y-m-d";

    public function summary()
    {

        $data['today'] = $this->fetchTodayExpense();
        $data['past_week'] = $this->fetchPastWeekExpense();
        $data['this_month'] = $this->fetchMonthExpense();
        $data['this_year'] = $this->fetchYearExpense();

        return $this->ok("Summary", $data);
    }

    private function fetchTodayExpense()
    {
        return Expense::where("user_id", Auth::id())
            ->whereDate("expense_date", now()->format($this->dateFormat))
            ->sum("amount");
    }

    private function fetchPastWeekExpense()
    {
        return Expense::where("user_id", Auth::id())
            ->whereDate("expense_date", ">=", now()->subWeek()->format($this->dateFormat))
            ->sum("amount");
    }

    private function fetchMonthExpense()
    {
        return Expense::where("user_id", Auth::id())
            ->whereDate("expense_date", ">=", now()->firstOfMonth()->format($this->dateFormat))
            ->sum("amount");
    }

    private function fetchYearExpense()
    {
        return Expense::where("user_id", Auth::id())
            ->whereDate("expense_date", ">=", now()->firstOfYear()->format($this->dateFormat))
            ->sum("amount");
    }
}


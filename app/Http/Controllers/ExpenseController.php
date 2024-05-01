<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    public function index()
    {

        $items = Expense::getByUser(50);

        return $this->ok("Expense List", $items);
    }

    public function store(ExpenseRequest $request)
    {

        $itemId = Item::getId($request->item);

        if (!$itemId) {
            Log::error("invalid item id used");
            return $this->error("Unable to save expense at the moment");
        }

        $newExpense = Expense::createExpense((object) [
            "item_id" => $itemId,
            "amount" => $request->amount,
            "date" => $request->date
        ]);

        return $this->ok("Expense saved", $newExpense);
    }

    public function destroy($expense_uuid) {

        $expense = Expense::getByUuid($expense_uuid);

        if (!$expense) {
            return $this->error("Expense does not exist", 404);
        }

        Expense::deleteExpense($expense);

        return $this->ok("Expense deleted");
    }
}

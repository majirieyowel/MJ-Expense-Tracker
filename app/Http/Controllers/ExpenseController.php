<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;
use App\Http\Requests\ExpenseRequest;
use App\Models\Expense;
use Illuminate\Support\Facades\Log;

class ExpenseController extends Controller
{
    public function createExpense(ExpenseRequest $request)
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
}

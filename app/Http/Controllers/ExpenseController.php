<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;
use App\Services\ExpenseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ExpenseRequest;

class ExpenseController extends Controller
{
    public function __construct(protected ExpenseService $expenseService)
    {
    }

    public function index(): JsonResponse
    {
        $items = Expense::getByUser(50);

        return $this->ok("Expense List", $items);
    }


    public function summary(): JsonResponse
    {
        return $this->ok("Expense summary", $this->expenseService->summary(auth()->id()));
    }

    public function topExpenses(Request $request)
    {
        return $this->getTopExpenses();
    }


    public function store(ExpenseRequest $request)
    {

        [$status, $output, $errorCode] = $this->expenseService->store($request->item, $request->amount, $request->date, Auth::id());

        return $status ? $this->getTopExpenses() : $this->error($output, $errorCode);
    }

    public function destroy($expense_uuid)
    {

        $expense = Expense::getByUuid($expense_uuid);

        if (!$expense) {
            return $this->error("Expense does not exist", 404);
        }

        Expense::deleteExpense($expense);

        return $this->ok("Expense deleted");
    }

    private function formatGroupKey(String $dateString)
    {

        $date = new \DateTime($dateString);

        if (now()->format('Y-m-d') === $dateString) {
            return "Today";
        };

        return $date->format('jS F Y');
    }

    private function formatExpense(Expense $expense)
    {
        return [
            'index' => $expense->id,
            'uuid' => $expense->uuid,
            'item' => $expense->item?->title,
            'amount' => $expense->amount,
            'date' => $expense->expense_date
        ];
    }

    private function getTopExpenses()
    {
        $topExpenses = Expense::getLatestExpenses();

        $itemsGroup = [];

        foreach ($topExpenses as $expense) {
            $itemsGroup[$expense->expense_date][] = $this->formatExpense($expense);
        }

        $formattedItems = [];

        foreach ($itemsGroup as $groupKey => $value) {



            $innerGroup = [];
            $innerGroup["key"] = $this->formatGroupKey($groupKey);
            $innerGroup["values"] = $this->sortByIndex($value);
            $formattedItems[] = $innerGroup;
        }

        return $this->ok('Latest expenses', $formattedItems);
    }

    public function sortByIndex($array)
    {
        // Extract the "index" column for sorting
        $indexes = array_column($array, 'index');

        // Sort the indexes in descending order
        rsort($indexes);

        // Reorder the original array based on the sorted indexes
        $sortedArray = [];
        foreach ($indexes as $index) {
            foreach ($array as $item) {
                if ($item['index'] === $index) {
                    $sortedArray[] = $item;
                    break;
                }
            }
        }

        return $sortedArray;
    }

    public function expenseChart(Request $request)
    {
        $year = $request->query('year', '2024');

        $monthlyExpenses = Expense::select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(amount) as total'))
            ->whereYear('created_at', $year)
            ->groupBy(DB::raw('MONTH(created_at)'))
            ->get();

        $monthlyExpensesWithDefaults = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthlyExpensesWithDefaults[$i] = 0;
        }
        
        foreach ($monthlyExpenses as $expense) {
            $monthlyExpensesWithDefaults[$expense->month] = floatval($expense->total);
        }

        $data['year'] = $year;
        $data['dataset'] = [...$monthlyExpensesWithDefaults];

        return $this->ok("Expense chart", $data);
    }
}

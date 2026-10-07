<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Expense::where('business_id', $this->businessId($request->user()));

        if ($request->filled('from')) {
            $query->whereDate('expense_date', '>=', $request->from);
        }

        if ($request->filled('to')) {
            $query->whereDate('expense_date', '<=', $request->to);
        }

        $expenses = $query->latest('expense_date')->paginate(15);

        $total = $query->sum('amount');

        return response()->json([
            'data' => [
                'total' => $total,
                'expenses' => $expenses->items(),
                'pagination' => [
                    'total' => $expenses->total(),
                    'per_page' => $expenses->perPage(),
                    'current_page' => $expenses->currentPage(),
                    'last_page' => $expenses->lastPage(),
                ],
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => ['sometimes', 'nullable', 'string', 'max:100'],
            'amount' => ['required', 'integer', 'min:0'],
            'note' => ['sometimes', 'nullable', 'string', 'max:500'],
            'expense_date' => ['sometimes', 'date'],
        ]);

        $expense = new Expense($validated);
        $expense->business_id = $this->businessId($request->user());
        $expense->user_id = $request->user()->id;
        $expense->expense_date = $validated['expense_date'] ?? now()->toDateString();
        $expense->save();

        return response()->json(['data' => ['expense' => $expense]], 201);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API controller for managing expenses.
 */
class ExpenseApiController extends Controller
{
    /**
     * List expenses for the authenticated user.
     * Supports filtering by date range, category, account.
     */
    public function index(Request $request)
    {
        $query = Expense::where('user_id', Auth::id());
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->filled('expense_category_id')) {
            $query->where('expense_category_id', $request->expense_category_id);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        return response()->json($query->latest()->paginate(20));
    }

    /**
     * Store a new expense record.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'channel' => 'required|in:bank,momo,cash,other',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);
        $data['user_id'] = Auth::id();
        $expense = Expense::create($data);
        return response()->json($expense, 201);
    }
}

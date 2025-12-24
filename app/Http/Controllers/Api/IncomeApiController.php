<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * API controller for managing incomes.
 */
class IncomeApiController extends Controller
{
    /**
     * List incomes for the authenticated user.
     * Optional query parameters: start_date, end_date, category_id, account_id.
     */
    public function index(Request $request)
    {
        $query = Income::where('user_id', Auth::id());
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }
        if ($request->filled('income_category_id')) {
            $query->where('income_category_id', $request->income_category_id);
        }
        if ($request->filled('account_id')) {
            $query->where('account_id', $request->account_id);
        }
        return response()->json($query->latest()->paginate(20));
    }

    /**
     * Store a new income record.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'income_category_id' => 'required|exists:income_categories,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'channel' => 'required|in:bank,momo,cash,other',
            'account_id' => 'required|exists:accounts,id',
            'notes' => 'nullable|string',
        ]);
        $data['user_id'] = Auth::id();
        $income = Income::create($data);
        return response()->json($income, 201);
    }
}

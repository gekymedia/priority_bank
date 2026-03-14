<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

/**
 * Handles CRUD operations for monthly budgets per expense category.
 */
class BudgetController extends Controller
{
    /**
     * Display a listing of budgets for the authenticated user. By default show current month.
     * Budget is now monthly total (spent from transactions ledger).
     */
    public function index(Request $request)
    {
        $month = $request->input('month', Carbon::now()->format('Y-m'));
        $budgets = Budget::where('user_id', Auth::id())
            ->where('month', $month)
            ->get();
        return view('budgets.index', compact('budgets', 'month'));
    }

    /**
     * Show form for creating a budget.
     */
    public function create()
    {
        return view('budgets.create');
    }

    /**
     * Store a new budget.
     */
    public function store(Request $request)
    {
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
        ]);

        Budget::updateOrCreate([
            'user_id' => Auth::id(),
            'month' => $request->month,
        ], [
            'amount' => $request->amount,
        ]);

        return redirect()->route('budgets.index', ['month' => $request->month])
            ->with('success', 'Budget saved successfully.');
    }

    /**
     * Show the form for editing a budget.
     */
    public function edit(Budget $budget)
    {
        $this->authorize('update', $budget);
        return view('budgets.edit', compact('budget'));
    }

    /**
     * Update a budget.
     */
    public function update(Request $request, Budget $budget)
    {
        $this->authorize('update', $budget);
        $request->validate([
            'month' => 'required|date_format:Y-m',
            'amount' => 'required|numeric|min:0',
        ]);
        $budget->update([
            'month' => $request->month,
            'amount' => $request->amount,
        ]);
        return redirect()->route('budgets.index', ['month' => $budget->month])
            ->with('success', 'Budget updated successfully.');
    }

    /**
     * Delete a budget.
     */
    public function destroy(Budget $budget)
    {
        $this->authorize('delete', $budget);
        $budget->delete();
        return redirect()->route('budgets.index')->with('success', 'Budget deleted successfully.');
    }
}

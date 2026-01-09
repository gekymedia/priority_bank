<?php

namespace App\Http\Controllers;

use App\Models\ExpenseCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ExpenseCategoryController extends Controller
{
    /**
     * Display a listing of expense categories.
     */
    public function index()
    {
        $categories = ExpenseCategory::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->orderBy('name')
            ->get();
        
        return view('expense-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new expense category.
     */
    public function create()
    {
        return view('expense-categories.create');
    }

    /**
     * Store a newly created expense category.
     */
    public function store(Request $request)
    {
        $userId = Auth::user()->isAdmin() ? null : Auth::id();
        
        // Custom validation for unique name with user_id
        $exists = ExpenseCategory::where('name', $request->name)
            ->where(function($query) use ($userId) {
                if ($userId === null) {
                    $query->whereNull('user_id');
                } else {
                    $query->where('user_id', $userId);
                }
            })
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['name' => 'This category name already exists.'])->withInput();
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        ExpenseCategory::create([
            'name' => $validated['name'],
            'user_id' => $userId,
        ]);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Expense category created successfully.');
    }

    /**
     * Show the form for editing the specified expense category.
     */
    public function edit(ExpenseCategory $expenseCategory)
    {
        // Only allow editing global categories (user_id null) if admin, or own categories
        if ($expenseCategory->user_id !== null && $expenseCategory->user_id !== Auth::id()) {
            abort(403);
        }

        return view('expense-categories.edit', compact('expenseCategory'));
    }

    /**
     * Update the specified expense category.
     */
    public function update(Request $request, ExpenseCategory $expenseCategory)
    {
        // Only allow updating global categories (user_id null) if admin, or own categories
        if ($expenseCategory->user_id !== null && $expenseCategory->user_id !== Auth::id()) {
            abort(403);
        }

        // Custom validation for unique name with user_id
        $exists = ExpenseCategory::where('name', $request->name)
            ->where('id', '!=', $expenseCategory->id)
            ->where(function($query) use ($expenseCategory) {
                if ($expenseCategory->user_id === null) {
                    $query->whereNull('user_id');
                } else {
                    $query->where('user_id', $expenseCategory->user_id);
                }
            })
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['name' => 'This category name already exists.'])->withInput();
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $expenseCategory->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('expense-categories.index')
            ->with('success', 'Expense category updated successfully.');
    }

    /**
     * Remove the specified expense category.
     */
    public function destroy(ExpenseCategory $expenseCategory)
    {
        // Only allow deleting global categories (user_id null) if admin, or own categories
        if ($expenseCategory->user_id !== null && $expenseCategory->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if category has expenses or budgets
        if ($expenseCategory->expenses()->count() > 0 || $expenseCategory->budgets()->count() > 0) {
            return redirect()->route('expense-categories.index')
                ->with('error', 'Cannot delete category that has associated expense records or budgets.');
        }

        $expenseCategory->delete();

        return redirect()->route('expense-categories.index')
            ->with('success', 'Expense category deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\IncomeCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeCategoryController extends Controller
{
    /**
     * Display a listing of income categories.
     */
    public function index()
    {
        $categories = IncomeCategory::whereNull('user_id')
            ->orWhere('user_id', Auth::id())
            ->orderBy('name')
            ->get();
        
        return view('income-categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new income category.
     */
    public function create()
    {
        return view('income-categories.create');
    }

    /**
     * Store a newly created income category.
     */
    public function store(Request $request)
    {
        $userId = Auth::user()->isAdmin() ? null : Auth::id();
        
        // Custom validation for unique name with user_id
        $exists = IncomeCategory::where('name', $request->name)
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

        IncomeCategory::create([
            'name' => $validated['name'],
            'user_id' => $userId,
        ]);

        return redirect()->route('income-categories.index')
            ->with('success', 'Income category created successfully.');
    }

    /**
     * Show the form for editing the specified income category.
     */
    public function edit(IncomeCategory $incomeCategory)
    {
        // Only allow editing global categories (user_id null) if admin, or own categories
        if ($incomeCategory->user_id !== null && $incomeCategory->user_id !== Auth::id()) {
            abort(403);
        }

        return view('income-categories.edit', compact('incomeCategory'));
    }

    /**
     * Update the specified income category.
     */
    public function update(Request $request, IncomeCategory $incomeCategory)
    {
        // Only allow updating global categories (user_id null) if admin, or own categories
        if ($incomeCategory->user_id !== null && $incomeCategory->user_id !== Auth::id()) {
            abort(403);
        }

        // Custom validation for unique name with user_id
        $exists = IncomeCategory::where('name', $request->name)
            ->where('id', '!=', $incomeCategory->id)
            ->where(function($query) use ($incomeCategory) {
                if ($incomeCategory->user_id === null) {
                    $query->whereNull('user_id');
                } else {
                    $query->where('user_id', $incomeCategory->user_id);
                }
            })
            ->exists();
            
        if ($exists) {
            return back()->withErrors(['name' => 'This category name already exists.'])->withInput();
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $incomeCategory->update([
            'name' => $validated['name'],
        ]);

        return redirect()->route('income-categories.index')
            ->with('success', 'Income category updated successfully.');
    }

    /**
     * Remove the specified income category.
     */
    public function destroy(IncomeCategory $incomeCategory)
    {
        // Only allow deleting global categories (user_id null) if admin, or own categories
        if ($incomeCategory->user_id !== null && $incomeCategory->user_id !== Auth::id()) {
            abort(403);
        }

        // Check if category has incomes
        if ($incomeCategory->incomes()->count() > 0) {
            return redirect()->route('income-categories.index')
                ->with('error', 'Cannot delete category that has associated income records.');
        }

        $incomeCategory->delete();

        return redirect()->route('income-categories.index')
            ->with('success', 'Income category deleted successfully.');
    }
}

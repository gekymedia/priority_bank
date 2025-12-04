<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IncomeController extends Controller
{
    /**
     * Display a listing of the user's incomes.
     */
    public function index()
    {
        $incomes = Income::where('user_id', Auth::id())->latest()->paginate(10);
        return view('income.index', compact('incomes'));
    }

    /**
     * Show the form for creating a new income.
     */
    public function create()
    {
        return view('income.create');
    }

    /**
     * Store a newly created income in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        Income::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'source' => $request->source,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income recorded successfully.');
    }

    /**
     * Show the form for editing the specified income.
     */
    public function edit(Income $income)
    {
        $this->authorize('update', $income);
        return view('income.edit', compact('income'));
    }

    /**
     * Update the specified income in storage.
     */
    public function update(Request $request, Income $income)
    {
        $this->authorize('update', $income);

        $request->validate([
            'amount' => 'required|numeric|min:0',
            'source' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        $income->update([
            'amount' => $request->amount,
            'source' => $request->source,
            'description' => $request->description,
            'date' => $request->date,
        ]);

        return redirect()->route('incomes.index')->with('success', 'Income updated successfully.');
    }

    /**
     * Remove the specified income from storage.
     */
    public function destroy(Income $income)
    {
        $this->authorize('delete', $income);

        $income->delete();

        return redirect()->route('incomes.index')->with('success', 'Income deleted successfully.');
    }
}

<?php

namespace App\Http\Controllers;
/**
 * @uses \Illuminate\Foundation\Auth\Access\AuthorizesRequests
 */
use App\Models\Transaction;
use App\Models\SystemRegistry;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $transactions = Transaction::where('user_id', auth()->id())
            ->when(request('type'), function ($query) {
                return $query->where('type', request('type'));
            })
            ->when(request('start_date'), function ($query) {
                return $query->where('date', '>=', request('start_date'));
            })
            ->when(request('end_date'), function ($query) {
                return $query->where('date', '<=', request('end_date'));
            })
            ->latest()
            ->paginate(15);

        return view('transactions.index', compact('transactions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $systems = SystemRegistry::active()->orderBy('name')->pluck('name', 'id');
        return view('transactions.create', [
            'categories' => [
                'income' => ['Salary', 'Bonus', 'Freelance', 'Investment'],
                'expense' => ['Food', 'Transport', 'Housing', 'Entertainment']
            ],
            'systems' => $systems
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'external_system_id' => 'nullable|exists:systems_registry,id'
        ]);

        Transaction::create([
            'user_id' => auth()->id(),
            'type' => $validated['type'],
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'date' => $validated['date'],
            'description' => $validated['description'],
            'external_system_id' => $validated['external_system_id'] ?? null
        ]);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction added successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);
        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        $this->authorize('update', $transaction);
        
        return view('transactions.edit', [
            'transaction' => $transaction,
            'categories' => [
                'income' => ['Salary', 'Bonus', 'Freelance', 'Investment'],
                'expense' => ['Food', 'Transport', 'Housing', 'Entertainment']
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $validated = $request->validate([
            'type' => 'required|in:income,expense',
            'category' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $transaction->update($validated);

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);
        
        $transaction->delete();

        return redirect()->route('transactions.index')
            ->with('success', 'Transaction deleted successfully!');
    }
}
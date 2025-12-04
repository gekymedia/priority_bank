<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoanController extends Controller
{
    public function index()
    {
        $loans = Loan::where('user_id', Auth::id())->latest()->get();
        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        return view('loans.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'borrower_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date'
        ]);

        Loan::create([
            'user_id' => Auth::id(),
            'borrower_name' => $request->borrower_name,
            'amount' => $request->amount,
            'description' => $request->description,
            'due_date' => $request->due_date,
        ]);

        return redirect()->route('loans.index')->with('success', 'Loan recorded successfully.');
    }

    public function edit(Loan $loan)
    {
        $this->authorize('update', $loan);
        return view('loans.edit', compact('loan'));
    }

    public function update(Request $request, Loan $loan)
    {
        $this->authorize('update', $loan);

        $request->validate([
            'borrower_name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date'
        ]);

        $loan->update($request->only(['borrower_name', 'amount', 'description', 'due_date']));

        return redirect()->route('loans.index')->with('success', 'Loan updated successfully.');
    }

    public function destroy(Loan $loan)
    {
        $this->authorize('delete', $loan);
        $loan->delete();

        return redirect()->route('loans.index')->with('success', 'Loan deleted.');
    }
}

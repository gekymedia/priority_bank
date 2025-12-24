<?php

namespace App\Http\Controllers;

use App\Models\InterestRate;
use Illuminate\Http\Request;

class InterestRatesController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $interestRates = InterestRate::latest()->paginate(20);

        return view('interest-rates.index', compact('interestRates'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('interest-rates.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rate_percentage' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:loan_interest,savings_interest',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string|max:500',
        ]);

        InterestRate::create($request->all());

        return redirect()->route('interest-rates.index')
            ->with('success', 'Interest rate created successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(InterestRate $interestRate)
    {
        return view('interest-rates.show', compact('interestRate'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(InterestRate $interestRate)
    {
        return view('interest-rates.edit', compact('interestRate'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, InterestRate $interestRate)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'rate_percentage' => 'required|numeric|min:0|max:100',
            'type' => 'required|in:loan_interest,savings_interest',
            'is_active' => 'boolean',
            'effective_from' => 'required|date',
            'effective_to' => 'nullable|date|after:effective_from',
            'description' => 'nullable|string|max:500',
        ]);

        $interestRate->update($request->all());

        return redirect()->route('interest-rates.index')
            ->with('success', 'Interest rate updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(InterestRate $interestRate)
    {
        // Check if rate is being used by any loans
        if ($interestRate->loans()->exists()) {
            return back()->withErrors(['interest_rate' => 'Cannot delete interest rate that is being used by loans.']);
        }

        $interestRate->delete();

        return redirect()->route('interest-rates.index')
            ->with('success', 'Interest rate deleted successfully!');
    }
}

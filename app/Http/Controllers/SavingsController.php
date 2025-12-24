<?php

namespace App\Http\Controllers;

use App\Models\Saving;
use App\Models\GroupFund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $savings = Saving::with('user')->latest()->paginate(20);
        } else {
            $savings = Saving::where('user_id', $user->id)->latest()->paginate(20);
        }

        return view('savings.index', compact('savings'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('savings.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'deposit_date' => 'required|date|before_or_equal:today',
            'notes' => 'nullable|string|max:500',
        ]);

        $saving = Saving::create([
            'user_id' => Auth::id(),
            'amount' => $request->amount,
            'deposit_date' => $request->deposit_date,
            'notes' => $request->notes,
            'status' => 'available',
        ]);

        // Update group funds
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();

        return redirect()->route('savings.index')
            ->with('success', 'Savings deposit recorded successfully!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Saving $saving)
    {
        $this->authorize('view', $saving);
        return view('savings.show', compact('saving'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Saving $saving)
    {
        $this->authorize('update', $saving);
        return view('savings.edit', compact('saving'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Saving $saving)
    {
        $this->authorize('update', $saving);

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'deposit_date' => 'required|date|before_or_equal:today',
            'status' => 'required|in:available,withdrawn,locked',
            'notes' => 'nullable|string|max:500',
        ]);

        $saving->update($request->only(['amount', 'deposit_date', 'status', 'notes']));

        // Update group funds
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();

        return redirect()->route('savings.index')
            ->with('success', 'Savings updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Saving $saving)
    {
        $this->authorize('delete', $saving);

        $saving->delete();

        // Update group funds
        $groupFund = GroupFund::getInstance();
        $groupFund->updateTotals();

        return redirect()->route('savings.index')
            ->with('success', 'Savings deleted successfully!');
    }
}

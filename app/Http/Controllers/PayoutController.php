<?php

namespace App\Http\Controllers;

use App\Models\Payout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayoutController extends Controller
{
    public function index()
    {
        $payouts = Payout::where('user_id', Auth::id())->latest()->get();
        return view('payouts.index', compact('payouts'));
    }

    public function create()
    {
        return view('payouts.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        Payout::create([
            'user_id' => Auth::id(),
            'purpose' => $request->purpose,
            'amount' => $request->amount,
            'description' => $request->description,
        ]);

        return redirect()->route('payouts.index')->with('success', 'Payout recorded successfully.');
    }

    public function edit(Payout $payout)
    {
        $this->authorize('update', $payout);
        return view('payouts.edit', compact('payout'));
    }

    public function update(Request $request, Payout $payout)
    {
        $this->authorize('update', $payout);

        $request->validate([
            'purpose' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string'
        ]);

        $payout->update($request->only(['purpose', 'amount', 'description']));

        return redirect()->route('payouts.index')->with('success', 'Payout updated successfully.');
    }

    public function destroy(Payout $payout)
    {
        $this->authorize('delete', $payout);
        $payout->delete();

        return redirect()->route('payouts.index')->with('success', 'Payout deleted.');
    }
}

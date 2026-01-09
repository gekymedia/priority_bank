<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class ApiKeyController extends Controller
{
    /**
     * Display a listing of the user's API keys.
     */
    public function index()
    {
        $tokens = Auth::user()->tokens()->latest()->get();
        
        return view('api-keys.index', compact('tokens'));
    }

    /**
     * Store a newly created API key.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $token = Auth::user()->createToken($request->name);

        return redirect()->route('api-keys.index')
            ->with('success', 'API key created successfully.')
            ->with('token', $token->plainTextToken);
    }

    /**
     * Remove the specified API key.
     */
    public function destroy($id)
    {
        $token = PersonalAccessToken::find($id);
        
        if (!$token || $token->tokenable_id !== Auth::id()) {
            return redirect()->route('api-keys.index')
                ->with('error', 'API key not found.');
        }

        $token->delete();

        return redirect()->route('api-keys.index')
            ->with('success', 'API key deleted successfully.');
    }

    /**
     * Show API documentation.
     */
    public function documentation()
    {
        $baseUrl = config('app.url');
        
        return view('api-keys.documentation', compact('baseUrl'));
    }
}

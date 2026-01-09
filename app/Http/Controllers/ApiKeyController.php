<?php

namespace App\Http\Controllers;

use App\Models\SystemRegistry;
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
        $systems = SystemRegistry::active()->orderBy('name')->get();
        
        return view('api-keys.index', compact('tokens', 'systems'));
    }

    /**
     * Store a newly created API key.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'system_id' => 'nullable|exists:systems_registry,system_id',
            'callback_url' => 'nullable|url|max:255',
        ]);

        $tokenResult = Auth::user()->createToken($request->name);
        $token = $tokenResult->accessToken;

        // If system_id is provided, update the system's callback_url
        if ($request->system_id) {
            $system = SystemRegistry::where('system_id', $request->system_id)->first();
            if ($system) {
                $updateData = [];
                
                // Update callback URL if provided
                if ($request->callback_url) {
                    $updateData['callback_url'] = rtrim($request->callback_url, '/');
                }
                
                // Store token ID in metadata for tracking (optional)
                $metadata = $system->metadata ?? [];
                $metadata['api_token_id'] = $token->id;
                $updateData['metadata'] = $metadata;
                
                $system->update($updateData);
            }
        }

        return redirect()->route('api-keys.index')
            ->with('success', 'API key created successfully.')
            ->with('token', $tokenResult->plainTextToken);
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

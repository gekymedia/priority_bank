<?php

namespace App\Http\Controllers;

use App\Models\SystemRegistry;
use Illuminate\Http\Request;

class SystemRegistryController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'system_id' => 'required|string|max:255|unique:systems_registry,system_id',
            'name' => 'required|string|max:255',
            'type' => 'required|in:manual,automated,hybrid',
            'callback_url' => 'nullable|url|max:255',
            'api_base_url' => 'nullable|url|max:255',
            'active_status' => 'boolean',
            'description' => 'nullable|string',
            'is_protected' => 'boolean',
        ]);

        $system = SystemRegistry::create($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Source created successfully!',
                'system' => $system
            ]);
        }

        return redirect()->route('api-keys.index')
            ->with('success', 'Source created successfully!');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, SystemRegistry $systemRegistry)
    {
        // Prevent editing protected sources
        if ($systemRegistry->is_protected) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Protected sources cannot be edited.'
                ], 403);
            }
            return redirect()->route('api-keys.index')
                ->with('error', 'Protected sources cannot be edited.');
        }

        $validated = $request->validate([
            'system_id' => 'required|string|max:255|unique:systems_registry,system_id,' . $systemRegistry->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:manual,automated,hybrid',
            'callback_url' => 'nullable|url|max:255',
            'api_base_url' => 'nullable|url|max:255',
            'active_status' => 'boolean',
            'description' => 'nullable|string',
        ]);

        $systemRegistry->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Source updated successfully!',
                'system' => $systemRegistry
            ]);
        }

        return redirect()->route('api-keys.index')
            ->with('success', 'Source updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SystemRegistry $systemRegistry)
    {
        // Prevent deleting protected sources
        if ($systemRegistry->is_protected) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Protected sources cannot be deleted.'
                ], 403);
            }
            return redirect()->route('api-keys.index')
                ->with('error', 'Protected sources cannot be deleted.');
        }

        $systemRegistry->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Source deleted successfully!'
            ]);
        }

        return redirect()->route('api-keys.index')
            ->with('success', 'Source deleted successfully!');
    }
}

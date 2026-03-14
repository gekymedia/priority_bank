<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\SystemRegistry;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SystemRegistryController extends Controller
{
    /**
     * Store a newly created resource in storage.
     * Creates a system user account for the source so it can be accounted for separately
     * (balance tracking, transactions) and links it to the new source.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'system_id' => 'required|string|max:255|unique:systems_registry,system_id',
            'account_number' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'type' => 'required|in:manual,automated,hybrid',
            'callback_url' => 'nullable|url|max:255',
            'api_base_url' => 'nullable|url|max:255',
            'active_status' => 'boolean',
            'description' => 'nullable|string',
            'is_protected' => 'boolean',
        ]);

        $system = DB::transaction(function () use ($validated) {
            $systemId = $validated['system_id'];
            $name = $validated['name'];
            $systemEmail = 'system.' . strtolower($systemId) . '@prioritybank.internal';
            $systemPhone = 'SYSTEM-' . $systemId;

            $user = User::create([
                'name' => $name . ' (System Account)',
                'email' => $systemEmail,
                'phone' => $systemPhone,
                'password' => Hash::make(Str::random(32)),
                'role' => 'user',
                'type' => 'system',
                'status' => 'approved',
                'preferred_currency' => 'GHS',
                'notification_email' => false,
                'notification_browser' => false,
                'notification_sms' => false,
                'notification_whatsapp' => false,
                'notification_gekychat' => false,
            ]);

            Account::create([
                'user_id' => $user->id,
                'name' => 'Default',
                'type' => 'bank',
                'opening_balance' => 0,
            ]);

            $validated['user_id'] = $user->id;
            return SystemRegistry::create($validated);
        });

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
            'account_number' => 'nullable|string|max:50',
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

    /**
     * Create a system user account for an existing source and link it.
     * Use when a source was created without a user (e.g. before the feature existed).
     */
    public function createUser(SystemRegistry $systemRegistry)
    {
        if ($systemRegistry->user_id) {
            return redirect()->route('api-keys.index')
                ->with('info', 'This source already has a linked user account.');
        }

        DB::transaction(function () use ($systemRegistry) {
            $systemId = $systemRegistry->system_id;
            $name = $systemRegistry->name;
            $systemEmail = 'system.' . strtolower($systemId) . '@prioritybank.internal';
            $systemPhone = 'SYSTEM-' . $systemId;

            $user = User::create([
                'name' => $name . ' (System Account)',
                'email' => $systemEmail,
                'phone' => $systemPhone,
                'password' => Hash::make(Str::random(32)),
                'role' => 'user',
                'type' => 'system',
                'status' => 'approved',
                'preferred_currency' => 'GHS',
                'notification_email' => false,
                'notification_browser' => false,
                'notification_sms' => false,
                'notification_whatsapp' => false,
                'notification_gekychat' => false,
            ]);

            $systemRegistry->update(['user_id' => $user->id]);
        });

        return redirect()->route('api-keys.index')
            ->with('success', 'User account created and linked to "' . $systemRegistry->name . '".');
    }

    /**
     * Link an existing user account to a source (system account).
     */
    public function linkUser(Request $request, SystemRegistry $systemRegistry)
    {
        if ($systemRegistry->user_id) {
            return redirect()->route('api-keys.index')
                ->with('info', 'This source already has a linked user account.');
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $systemRegistry->update(['user_id' => $validated['user_id']]);

        return redirect()->route('api-keys.index')
            ->with('success', 'User account linked to "' . $systemRegistry->name . '".');
    }
}

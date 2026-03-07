<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Serve profile photo from storage (avoids 403 when public/storage is blocked on server).
     */
    public function showPhoto(string $path): Response|RedirectResponse
    {
        // Only allow paths under profile-photos/ and block path traversal
        $path = ltrim($path, '/');
        if (str_starts_with($path, 'profile-photos/') === false || str_contains($path, '..')) {
            abort(404);
        }

        if (!Storage::disk('public')->exists($path)) {
            abort(404);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $mimes = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
            'gif' => 'image/gif', 'webp' => 'image/webp', 'svg' => 'image/svg+xml',
        ];
        $mime = $mimes[strtolower($extension)] ?? 'image/jpeg';
        $content = Storage::disk('public')->get($path);

        return response($content, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=86400',
        ]);
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();

        // Handle file upload
        if ($request->hasFile('photo')) {
            // Delete old file if exists
            if ($user->profile_photo_path) {
                Storage::delete('public/'.$user->profile_photo_path);
            }

            // Store new file
            $path = $request->file('photo')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        // Convert checkbox values to boolean for notification preferences
        $data['notification_email'] = isset($data['notification_email']) && $data['notification_email'] == '1';
        $data['notification_browser'] = isset($data['notification_browser']) && $data['notification_browser'] == '1';
        $data['notification_sms'] = isset($data['notification_sms']) && $data['notification_sms'] == '1';
        $data['notification_whatsapp'] = isset($data['notification_whatsapp']) && $data['notification_whatsapp'] == '1';
        $data['notification_gekychat'] = isset($data['notification_gekychat']) && $data['notification_gekychat'] == '1';
        $data['theme'] = $data['theme'] ?? $user->theme;

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('profile.edit')->with('status', 'profile-updated');
    }
    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

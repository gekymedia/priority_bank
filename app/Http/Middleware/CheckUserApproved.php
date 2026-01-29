<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserApproved
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admins are always approved
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user is approved
        if (!$user->isApproved()) {
            // Logout the user if they're not approved
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            $response = redirect()->route('login')
                ->with('error', 'Your account is pending admin approval. Please wait for approval before accessing the system.');
            
            // Add cache-control headers to prevent browser caching
            $response->headers->set('Cache-Control', 'no-cache, no-store, max-age=0, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
            
            return $response;
        }

        return $next($request);
    }
}

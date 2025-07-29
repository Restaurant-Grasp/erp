<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Only allow super_admin or hr_manager
        if ($user->hasRole('super_admin') || $user->hasRole('hr_manager')) {
            return $next($request);
        }

        // AJAX request ku JSON error
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Otherwise redirect with error
        return redirect()->route('dashboard')->with('error', 'You do not have permission to access this resource.');
    }
}

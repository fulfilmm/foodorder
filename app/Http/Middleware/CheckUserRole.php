<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next,$roles): Response
    {
        // Check if the user is authenticated
        if (!Auth::check()) {
            abort(403, 'Unauthorized access.');
        }

        $user = Auth::user();



        if (empty($roles)) {
            abort(403, 'Unauthorized access.');
        }

        if ($user->role !== $roles) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}

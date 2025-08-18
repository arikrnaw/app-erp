<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $user = Auth::user();

        if (!$user->hasRole($role)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Insufficient permissions'], 403);
            }

            abort(403, 'Insufficient permissions');
        }

        return $next($request);
    }
}

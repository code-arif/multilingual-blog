<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        $user = auth()->user();

        if (!in_array($user->role, $roles)) {
            return response()->json([
                'status' => false,
                'message' => 'Access denied. Insufficient permissions.',
            ], 403);
        }

        return $next($request);
    }
}

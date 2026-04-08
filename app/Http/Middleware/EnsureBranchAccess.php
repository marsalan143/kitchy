<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBranchAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Admin can access all branches
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Branch Manager and Staff restricted to their branch
        if (!$user->branch_id) {
            return response()->json(['message' => 'User not assigned to a branch'], 403);
        }

        return $next($request);
    }
}

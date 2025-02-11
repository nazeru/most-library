<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Models\User;

class UserRoleMiddleware
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // Log::info('Проверка роли пользователя', [
        //     'user_role' => $user->role,
        //     'expected_role' => $role
        // ]);

        if (! $user->hasRole($role)) {
            return response()->json([
                'error' => 'Forbidden - Invalid Role',
                'user_role' => $user->role,
                'required_role' => $role
            ], 403);
        }

        return $next($request);
    }
}

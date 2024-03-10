<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthorizationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if($user->hasRole('Administrateur')) {
            return $next($request);
        }

        return response('Unauthorized. You do not have the required role.', 403);
    }
}

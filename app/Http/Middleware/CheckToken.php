<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckToken
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $token = $request->input('token');
        $localToken = env('ACCESS_TOKEN');

        if (!$token || $token !== $localToken) {
            return redirect()->route('access.denied'); // Crie uma rota de acesso negado
        }

        return $next($request);
    }
}

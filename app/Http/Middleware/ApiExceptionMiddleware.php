<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiExceptionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    // : Response
    {

        try {
            //code...
            return $next($request);
        } catch (\Throwable $th) {
            //throw $th;
            Log::error('Failed to create product', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'request' => $request->all(),
                'params' => $request->route()->parameters(),
                'headers' => $request->headers->all(),
                'method' => $request->method(),
                'uri' => $request->getRequestUri(),
                'route' => $request->route()->getName(),
                'user_id' => Auth::id() ?? null,
                'ip' => $request->ip()
                // 'exception' => $th
            ]);
        }


    }
}

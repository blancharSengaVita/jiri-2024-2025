<?php

namespace App\Http\Middleware;

use App\Models\Attendance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class VerifyEvaluatorToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session('attendance')) {
            return $next($request);
        }

        if (Auth::check()) {
            return redirect(route('pages.dashboard', absolute: false));
        }

        if (!$request->token) {
            abort('403');
        }

        $attendance = Attendance::where('token', $request->token)->first();
        if (!$attendance) {
            abort('404');
        }

        session(['attendance' => $attendance]);

        return $next($request);
    }
}

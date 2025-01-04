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
        if (Auth::check()) {
            return redirect(route('pages.dashboard', absolute: false));
        }

        if (!$request->token) {
            abort('404');
        }

        $attendance = Attendance::where('token', $request->token)->first();
        if (!$attendance) {
            abort('403');
        }

        session(['attendance' => $attendance]);

        return $next($request);
    }
}

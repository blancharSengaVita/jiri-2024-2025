<?php

namespace App\Http\Middleware;

use App\Models\Attendance;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class isEvaluator
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        if ($request->token) {
            $evaluator = Attendance::where('token', $request->token)->first();

            if (!$evaluator) {
                abort('404');
            }else {
                session(['evaluator' => $evaluator]);
            };
        }

        if (!session('evaluator')) {
            abort('403');
        }

        return $next($request);
    }
}

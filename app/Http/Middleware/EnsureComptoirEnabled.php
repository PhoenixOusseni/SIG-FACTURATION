<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureComptoirEnabled
{
    /**
     * Block comptoir routes when COMPTOIR_ENABLED is false.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!config('comptoir.enabled')) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Le module Facturation Comptoir est désactivé.',
                ], 403);
            }

            abort(403, 'Le module Facturation Comptoir est désactivé.');
        }

        return $next($request);
    }
}

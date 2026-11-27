<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('seller')) {
            abort(403, 'Satıcı paneline erişim yetkiniz bulunmamaktadır.');
        }

        $seller = $user->seller;

        if (! $seller || $seller->status !== 'approved') {
            abort(403, 'Satıcı paneline yalnızca onaylı satıcılar erişebilir.');
        }

        return $next($request);
    }
}

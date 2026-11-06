<?php

namespace App\Http\Middleware;

use App\Enums\SellerStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApprovedSeller
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->hasRole('seller'))
        {
            abort(403, 'Bu alana yalnızca satıcılar erişebilir.');
        }

        $seller = $user->seller;

        if (! $seller || $seller->status !== SellerStatus::Approved)
        {
            abort(403, 'Satıcı hesabınız henüz yönetici tarafından onaylanmamıştır.');
        }

        return $next($request);
    }
}

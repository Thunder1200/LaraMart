<?php

namespace App\Http\Controllers\Seller;

use App\Actions\Sellers\ApplyAsSellerAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Seller\ApplySellerRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function create(Request $request): View
    {
        $seller = $request->user()?->seller;

        return view('seller.apply', [
            'seller' => $seller,
        ]);
    }

    public function store(ApplySellerRequest $request, ApplyAsSellerAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated());

        return redirect()
            ->route('seller.apply.create')
            ->with('status', 'Satıcı başvurunuz alındı. Admin onayından sonra satıcı paneliniz aktif olacaktır.');
    }
}

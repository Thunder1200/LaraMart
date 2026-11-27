<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $seller = $request->user()?->seller?->loadCount('products');

        return view('seller.dashboard', [
            'seller' => $seller,
        ]);
    }
}

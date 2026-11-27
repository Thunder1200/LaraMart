<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user()?->loadCount(['addresses', 'favorites', 'orders']);

        return view('customer.account', [
            'user' => $user,
        ]);
    }
}

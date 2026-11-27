<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Catalog\GetHomepageDataAction;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(GetHomepageDataAction $action): View
    {
        return view('welcome', $action->execute());
    }
}

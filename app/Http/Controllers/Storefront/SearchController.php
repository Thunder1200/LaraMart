<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Catalog\SearchProductsAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function __invoke(Request $request, SearchProductsAction $action): View
    {
        $filters = $request->only([
            'q',
            'kategori',
            'marka',
            'min',
            'max',
            'renk',
            'beden',
            'puan',
            'sirala',
        ]);

        $result = $action->execute($filters);

        return view('storefront.search', [
            'filters' => $filters,
            'products' => $result['products'],
            'filterOptions' => $result['filterOptions'],
        ]);
    }
}

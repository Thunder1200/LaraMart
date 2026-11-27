<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Catalog\SearchProductsAction;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Request $request, Category $category, SearchProductsAction $action): View
    {
        $category->load(['parent', 'children']);

        $filters = $request->only([
            'marka',
            'min',
            'max',
            'renk',
            'beden',
            'puan',
            'sirala',
        ]);

        $sort = (string) ($filters['sirala'] ?? 'en-yeni');
        $allowedSorts = ['en-yeni', 'fiyat-artan', 'fiyat-azalan', 'en-cok-degerlendirilen'];
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'en-yeni';
            $filters['sirala'] = $sort;
        }

        $result = $action->execute($filters, $category);

        return view('storefront.category', [
            'category' => $category,
            'sort' => $sort,
            'filters' => $filters,
            'products' => $result['products'],
            'filterOptions' => $result['filterOptions'],
        ]);
    }
}

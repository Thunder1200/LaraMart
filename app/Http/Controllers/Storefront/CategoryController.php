<?php

namespace App\Http\Controllers\Storefront;

use App\Actions\Catalog\GetCategoryProductsAction;
use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function show(Request $request, Category $category, GetCategoryProductsAction $action): View
    {
        $category->load(['parent', 'children']);

        $sort = (string) $request->query('sirala', 'en-yeni');
        $allowedSorts = ['en-yeni', 'fiyat-artan', 'fiyat-azalan', 'en-cok-degerlendirilen'];

        if (! in_array($sort, $allowedSorts, true)) {
            $sort = 'en-yeni';
        }

        return view('storefront.category', [
            'category' => $category,
            'sort' => $sort,
            'products' => $action->execute($category, $sort),
        ]);
    }
}

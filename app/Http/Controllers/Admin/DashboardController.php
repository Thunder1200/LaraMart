<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'sellers' => Seller::count(),
                'categories' => Category::count(),
                'products' => Product::count(),
                'pending_reviews' => Review::query()->where('is_approved', false)->count(),
            ],
        ]);
    }
}

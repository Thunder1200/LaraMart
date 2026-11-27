<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Categories\CreateCategoryAction;
use App\Actions\Categories\DeleteCategoryAction;
use App\Actions\Categories\UpdateCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function index(): View
    {
        Gate::authorize('viewAny', Category::class);

        $categories = Category::query()
            ->with('parent')
            ->withCount(['children', 'products'])
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', [
            'categories' => $categories,
        ]);
    }

    public function create(): View
    {
        Gate::authorize('create', Category::class);

        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', [
            'parentCategories' => $parentCategories,
        ]);
    }

    public function store(StoreCategoryRequest $request, CreateCategoryAction $action): RedirectResponse
    {
        $action->execute($request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Kategori başarıyla oluşturuldu.');
    }

    public function edit(Category $category): View
    {
        Gate::authorize('update', $category);

        $parentCategories = Category::query()
            ->whereNull('parent_id')
            ->whereKeyNot($category->id)
            ->orderBy('sort')
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', [
            'category' => $category,
            'parentCategories' => $parentCategories,
        ]);
    }

    public function update(
        UpdateCategoryRequest $request,
        Category $category,
        UpdateCategoryAction $action
    ): RedirectResponse {
        $action->execute($category, $request->validated());

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Kategori başarıyla güncellendi.');
    }

    public function destroy(Category $category, DeleteCategoryAction $action): RedirectResponse
    {
        Gate::authorize('delete', $category);

        try {
            $action->execute($category);
        } catch (DomainException $exception) {
            return redirect()
                ->route('admin.categories.index')
                ->with('error', $exception->getMessage());
        }

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Kategori başarıyla silindi.');
    }
}

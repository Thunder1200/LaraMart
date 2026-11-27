<?php

namespace App\Actions\Categories;

use App\Models\Category;

class UpdateCategoryAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(Category $category, array $data): Category
    {
        $category->update([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'commission_rate' => $data['commission_rate'],
            'sort' => $data['sort'],
        ]);

        return $category->refresh();
    }
}

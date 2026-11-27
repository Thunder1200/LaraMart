<?php

namespace App\Actions\Categories;

use App\Models\Category;

class CreateCategoryAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data): Category
    {
        return Category::create([
            'parent_id' => $data['parent_id'] ?? null,
            'name' => $data['name'],
            'slug' => $data['slug'],
            'commission_rate' => $data['commission_rate'],
            'sort' => $data['sort'],
        ]);
    }
}

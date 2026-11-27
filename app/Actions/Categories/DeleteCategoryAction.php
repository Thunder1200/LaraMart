<?php

namespace App\Actions\Categories;

use App\Models\Category;
use DomainException;

class DeleteCategoryAction
{
    public function execute(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new DomainException('Bu kategoriye bağlı ürünler bulunduğu için silinemez.');
        }

        $category->delete();
    }
}

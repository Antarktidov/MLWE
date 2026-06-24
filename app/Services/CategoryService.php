<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Category;

class CategoryService {
    public function getCategories(Article $article)
    {
        $ids = explode(',', trim($article->categories_ids, '[]'));
        return Category::findMany($ids);
    }
}
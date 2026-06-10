<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function remove_category_from_article(Category $category, Article $article) {
        $sql = "UPDATE articles SET categories = array_remove(categories, ?) WHERE id = ?;";
        DB::state($sql, [$category->id, $article->id]);
        return ["category removed"];
    }
}

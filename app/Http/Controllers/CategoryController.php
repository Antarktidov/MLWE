<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Article;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function remove_category_from_article(Category $category, Article $article) {
        $sql = "UPDATE articles SET categories_ids = array_remove(categories_ids, ?) WHERE id = ?;";
        DB::statement($sql, [$category->id, $article->id]);
        return ["category removed"];
    }

    public function add_category(Request $request, Article $article) {
        $data = $request->validate([
            'category_name' => 'string|required',
        ]);
        $category_name = $data['category_name'];

        $category = Category::where('name', $category_name)->first();
        if ($category == null) {
            $category = Category::create([
                'name' => $category_name,
                ]);
        }

        $sql = "UPDATE articles SET categories_ids = array_append(categories_ids, ?) WHERE id = ?;";
        DB::statement($sql, [$category->id, $article->id]);

        return ["category added"];
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Category;
use App\Models\Wiki;

class CategoriesPageTest extends TestCase
{
    use RefreshDatabase;
    public function test_category_page(): void
    {
        $wiki = Wiki::factory()->create();

        /*$article = Article::factory()->create([
            'wiki_id' => $wiki->id,
        ]);*/

        /*$revision = Revision::factory()->create([
            'article_id' => $article->id,
            'url_title' => $article->url_title,
            'title' => $article->title,
            'user_id' => 0,
            'user_ip' => '127.0.0.1',
            'is_approved' => true,
            'is_patrolled' => true,
        ]);*/

        $category = Category::factory()->create();

        $response = $this->get("/wiki/{$wiki->url}/category/{$category->name}");

        $response->assertStatus(200);
    }
}

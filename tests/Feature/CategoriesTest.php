<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\Wiki;
use App\Models\Article;
use App\Models\Revision;


class CategoriesTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_category_can_be_added(): void
    {

        $wiki = Wiki::factory()->create();

        $article = Article::factory()->create([
            'wiki_id' => $wiki->id,
        ]);

        $revision = Revision::factory()->create([
            'article_id' => $article->id,
            'url_title' => $article->url_title,
            'title' => $article->title,
            'user_id' => 0,
            'user_ip' => '127.0.0.1',
            'is_approved' => true,
            'is_patrolled' => true,
        ]);

        $category = [
            'category_name' => 'Test category 1',
        ];

        $response = $this->post("/api/add_category/{$article->id}", $category);

        $response->assertStatus(200);

        $this->assertDatabaseHas('categories', [
            'name' => $category['category_name'],
        ]);
    }
}

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

        $category = Category::factory()->create();

        $response = $this->get("/wiki/{$wiki->url}/category/{$category->name}");

        $response->assertStatus(200);
    }
}

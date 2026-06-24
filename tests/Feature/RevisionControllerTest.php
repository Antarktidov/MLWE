<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Wiki;
use App\Models\Article;
use App\Models\Revision;

class RevisionControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_tha_user_can_view_article_revision(): void
    {
        $user = User::factory()->create();
        $wiki = Wiki::factory()->create();

        $article = Article::factory()->create([
            'wiki_id' => $wiki->id,
        ]);
        $revision = Revision::factory()->create([
            'article_id' => $article->id,
            'user_ip' => '127.0.0.1',
            'user_id' => $user->id,
            'title' =>  $article->title,
            'url_title' =>  $article->url_title,
            'is_approved' => true,
            'is_patrolled' => true,
        ]);

        $response = $this->get("/wiki/{$wiki->url}/article/{$article->url_title}/revision/{$revision->id}");

        $response->assertStatus(200);
    }
}

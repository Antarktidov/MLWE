<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\Wiki;
use App\Models\Article;
use App\Models\Revision;
use App\Models\UserGroup;
use App\Models\UserUserGroupWiki;

class RevisionControllerTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_user_can_view_article_revision(): void
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

    public function test_that_user_with_approvers_right_can_approve_revision(): void
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
            'is_approved' => false,
            'is_patrolled' => false,
        ]);

        $ug = UserGroup::factory()->create([
            'is_global' => false,
            'can_check_revisions' => true,
            'name' => 'approver',
        ]);

        UserUserGroupWiki::factory()->create([
            'wiki_id' => $wiki->id,
            'user_id' => $user->id,
            'user_group_id' => $ug->id,
        ]);

        $this->actingAs($user);

        $response = $this->post("/wiki/{$wiki->url}/{$article->url_title}/{$revision->id}/approve");

        $response->assertStatus(200);

        $this->assertDatabaseHas('revisions', [
            'article_id' => $article->id,
            'user_ip' => '127.0.0.1',
            'user_id' => $user->id,
            'title' =>  $article->title,
            'url_title' =>  $article->url_title,
            'is_approved' => true,
            'is_patrolled' => false,
        ]);
    }

    public function test_that_user_with_patrol_right_can_patrol_revision(): void
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
            'is_patrolled' => false,
        ]);

        $ug = UserGroup::factory()->create([
            'is_global' => false,
            'can_patrol_revisions' => true,
            'name' => 'approver',
        ]);

        UserUserGroupWiki::factory()->create([
            'wiki_id' => $wiki->id,
            'user_id' => $user->id,
            'user_group_id' => $ug->id,
        ]);

        $this->actingAs($user);

        $response = $this->post("/wiki/{$wiki->url}/{$article->url_title}/{$revision->id}/patrol");

        $response->assertStatus(200);

        $this->assertDatabaseHas('revisions', [
            'article_id' => $article->id,
            'user_ip' => '127.0.0.1',
            'user_id' => $user->id,
            'title' =>  $article->title,
            'url_title' =>  $article->url_title,
            'is_approved' => true,
            'is_patrolled' => true,
        ]);
    }
}

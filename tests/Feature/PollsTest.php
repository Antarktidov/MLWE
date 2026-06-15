<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserUserGroupWiki;
use App\Models\Wiki;
use App\Models\Poll;

class PollsTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_polls_can_be_created(): void
    {
        $data = [
            'title' => 'test poll',
            'variants' => ['variant 1', 'variant 2'],
        ];

        $user = User::factory()->create();
        $wiki = Wiki::factory()->create();

        $user_group = UserGroup::factory()->create([
            'is_global' => 1,
            'can_manage_polls' => 1,
        ]);

        $user_user_group_wiki = UserUserGroupWiki::factory()->create([
            'user_id' => $user->id,
            'user_group_id' => $user_group->id,
            'wiki_id' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->post('/polls/store', $data);

        $response->assertStatus(200);
    }

    public function test_that_user_can_vote_in_poll(): void
    {
        $data = [
            'title' => 'test poll',
            'variants' => ['variant 1', 'variant 2'],
        ];

        $user = User::factory()->create();
        $wiki = Wiki::factory()->create();

        $poll = Poll::factory()->create([
            'user_id' => $user->id
        ]);

        $this->actingAs($user);

        $data = [
            'variant_idx' => '1',
        ];

        $response =  $this->post("/api/polls/accept_vote/{$poll->id}", $data);

        $response->assertStatus(200);
    }
}

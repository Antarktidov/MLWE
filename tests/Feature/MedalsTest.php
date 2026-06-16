<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\UserUserGroupWiki;
use App\Models\UserGroup;
use App\Models\Wiki;
use App\Models\Medal;

class MedalsTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_user_with_rights_can_give_medal(): void
    {
        $giver = User::factory()->create();
        $receiver = User::factory()->create();

        $medal = Medal::factory()->create();

        $data = [
            'medal' => $medal->id,
        ];

        $wiki = Wiki::factory()->create();
        $u_group = UserGroup::factory()->create([
            'is_global' => 1,
            'can_manage_global_medals' => 1,
        ]);

        $uugw = UserUserGroupWiki::factory()->create([
            'wiki_id' => 0,
            'user_id' => $giver->id,
            'user_group_id' => $u_group->id,
        ]);

        $this->actingAs($giver);

        $response = $this->post("/give-medal/{$receiver->id}/", $data);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_medals', [
            'user_id' => $receiver->id,
            'giver_id' => $giver->id,
            'wiki_id' => 0,
            'medal_id' => $medal->id, 
        ]);
    }

    public function test_that_user_with_local_rights_can_give_local_medal(): void
    {
        $giver = User::factory()->create();
        $receiver = User::factory()->create();

        $wiki = Wiki::factory()->create();
        $medal = Medal::factory()->create([
            'wiki_id' => $wiki->id,
        ]);

        $data = [
            'medal' => $medal->id,
        ];

        $u_group = UserGroup::factory()->create([
            'is_global' => 0,
            'can_manage_medals' => 1,
        ]);

        $uugw = UserUserGroupWiki::factory()->create([
            'wiki_id' => $wiki->id,
            'user_id' => $giver->id,
            'user_group_id' => $u_group->id,
        ]);

        $this->actingAs($giver);

        $response = $this->post("wiki/{$wiki->url}/give-medal/{$receiver->id}/", $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('user_medals', [
            'user_id' => $receiver->id,
            'giver_id' => $giver->id,
            'wiki_id' => $wiki->id,
            'medal_id' => $medal->id, 
        ]);
    }
}

<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserUserGroupWiki;
use App\Models\Wiki;

class PermissionManagerTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_user_with_specific_right_can_manage_permissions(): void
    {
        $wiki = Wiki::factory()->create([]);

        $user = User::factory()->create([]);

        $usergroup = UserGroup::factory()->create([
            'can_manage_permissions' => 1,
            'is_global' => 1,
        ]);

        UserUserGroupWiki::factory()->create([
            'user_id' => $user->id,
            'user_group_id' => $usergroup->id,
            'wiki_id' => 0,
        ]);

        $this->actingAs($user);

        $data = [
            'user-group-names' => ['test'],
            'user-group-is-global' => [0],
            'user-group-permissions' => ['1_can_be_banan'],
        ];

        $response = $this->post('/permissions_manager/store', $data);

        $response->assertStatus(200);
    }
}

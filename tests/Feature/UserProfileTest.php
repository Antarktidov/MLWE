<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Wiki;

class UserProfileTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_global_profile_is_assessable(): void
    {
        $user = User::factory()->create();

        $response = $this->get("userprofile-global/{$user->id}");

        $response->assertStatus(200);
    }

    public function test_that_local_is_assessable(): void
    {
        $user = User::factory()->create();

        $wiki = Wiki::factory()->create();

        $response = $this->get("/wiki/{$wiki->url}/userprofile/{$user->id}");

        $response->assertStatus(200);
    }

    public function test_that_user_can_update_his_profile(): void
    {
        $user = User::factory()->create();

        $wiki = Wiki::factory()->create();

        $this->actingAs($user);

        $response = $this->post("/userprofile-global/{$user->id}/store");

        $response->assertStatus(200);
    }

    public function test_that_user_can_update_his_local_profile(): void
    {
        $user = User::factory()->create();

        $wiki = Wiki::factory()->create();

        $this->actingAs($user);

        $response = $this->post("/userprofile-global/{$user->id}/store");

        $response->assertStatus(200);
    }
}

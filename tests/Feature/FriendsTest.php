<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\FriendsRequest;

class FriendsTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_get_user_friends(): void
    {
        $friend1 = User::factory()->create();
        $friend2 = User::factory()->create();

        $sql = <<<SQL
        INSERT INTO friends (friends) VALUES (ARRAY[?::BIGINT, ?::BIGINT]);
        SQL;
        DB::statement($sql, [$friend1->id, $friend2->id]);

        $response = $this->get("/api/user/friends/{$friend1->id}");

        $response->assertStatus(200);
    }

    public function test_creation_of_friend_request(): void
    {
        $friend1 = User::factory()->create();
        $friend2 = User::factory()->create();

        $this->actingAs($friend1);

        $response = $this->post("/api/friends/add_friend/{$friend2->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('friends_requests', [
            'requester_id' => $friend1->id,
            'recipient_id' => $friend2->id,
            'status' => 'pending',
        ]);
    }

    public function test_that_user_can_accept_friend_requests(): void
    {
        $friend1 = User::factory()->create();
        $friend2 = User::factory()->create();

        $fr = FriendsRequest::factory()->create([
            'requester_id' => $friend1->id,
            'recipient_id' => $friend2->id,
            'status' => 'pending',
        ]);

        $this->actingAs($friend2);

        $response = $this->post("/api/friends/accept_friend_request/{$friend1->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('friends_requests', [
            'requester_id' => $friend1->id,
            'recipient_id' => $friend2->id,
            'status' => 'accepted',
        ]);
    }

    public function test_that_user_can_decline_friend_requests(): void
    {
        $friend1 = User::factory()->create();
        $friend2 = User::factory()->create();

        $fr = FriendsRequest::factory()->create([
            'requester_id' => $friend1->id,
            'recipient_id' => $friend2->id,
            'status' => 'pending',
        ]);

        $this->actingAs($friend2);

        $response = $this->post("/api/friends/decline_friend_request/{$friend1->id}");

        $response->assertStatus(200);
        
        $this->assertDatabaseHas('friends_requests', [
            'requester_id' => $friend1->id,
            'recipient_id' => $friend2->id,
            'status' => 'declined',
        ]);
    }
}

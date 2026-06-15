<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;

use App\Models\User;

class FriendsTests extends TestCase
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
}

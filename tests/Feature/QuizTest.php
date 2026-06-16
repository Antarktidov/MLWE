<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserUserGroupWiki;
use App\Models\Wiki;

class QuizTest extends TestCase
{
    use RefreshDatabase;
    public function test_that_user_can_create_quiz(): void
    {
        $data = [
            'title' => 'test quiz',
            'questions' => [
                [
                    'question' => 'Test question',
                    'answer' => 'Correct answer',
                    'wrong_answers' => [
                    'Wrong answer'
                    ],
                ],
                [
                    'question' => 'Test question',
                    'answer' => 'Correct answer',
                    'wrong_answers' => [
                    'Wrong answer'
                    ],
                ],
                [
                    'question' => 'Test question',
                    'answer' => 'Correct answer',
                    'wrong_answers' => [
                    'Wrong answer'
                    ],
                ],
            ],
        ];

        $user = User::factory()->create();
        $wiki = Wiki::factory()->create();

        $user_group = UserGroup::factory()->create([
            'is_global' => 1,
            'can_manage_trivia' => 1,
        ]);

        $user_user_group_wiki = UserUserGroupWiki::factory()->create([
            'user_id' => $user->id,
            'user_group_id' => $user_group->id,
            'wiki_id' => 0,
        ]);

        $this->actingAs($user);

        $response = $this->post('/quizes/store', $data);

        $response->assertStatus(200);
    }
}

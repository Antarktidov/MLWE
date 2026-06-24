<?php

namespace App\Services;

use App\Models\Wiki;

class UserService {
    public function getUserInfo($user, Wiki $wiki)
    {
        if (!$user) {
            return (object)[
                'id' => 0,
                'name' => 'Анонимный участник',
                'can_delete_comments' => false,
            ];
        }

        return (object)[
            'id' => $user->id,
            'name' => $user->name,
            'can_delete_comments' => $user->can('delete_comments', $wiki->url),
        ];
    }
}
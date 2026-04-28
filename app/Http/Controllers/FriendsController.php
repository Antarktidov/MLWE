<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\UserProfileRevision;

class FriendsController extends Controller
{
    public function get_user_friends(User $user) {
        $user_id = $user->id;
        $sql = <<<SQL
            SELECT COALESCE(
                JSON_AGG(
                    JSON_BUILD_OBJECT(
                        'id', friend_user_id,
                        'name', u.name,
                        'avatar', upr.avatar
                    )
                    ORDER BY friend_user_id
                ),
                '[]'::json
            ) AS user_friends
            FROM (
                SELECT
                    CASE
                        WHEN f.friends[1] = ? THEN f.friends[2]
                        WHEN f.friends[2] = ? THEN f.friends[1]
                    END AS friend_user_id
                FROM friends f
                WHERE ? = ANY(f.friends)
            ) friends_list
            JOIN users u ON u.id = friends_list.friend_user_id
            LEFT JOIN LATERAL (
                SELECT avatar
                FROM user_profile_revisions
                WHERE user_id = u.id
                  AND is_approved = TRUE
                  AND deleted_at IS NULL
                ORDER BY created_at DESC, id DESC
                LIMIT 1
            ) upr ON TRUE;
        SQL;

        $friends = DB::selectOne($sql, [$user_id, $user_id, $user_id]);
        $friendsPayload = $friends?->user_friends ?? '[]';

        if (is_string($friendsPayload)) {
            $friendsPayload = json_decode($friendsPayload, true) ?? [];
        }

        return [
            'user_friends' => $friendsPayload,
        ];
    }
}

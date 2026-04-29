<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\User;
use App\Models\UserProfileRevision;
use App\Models\FriendsRequest;

use App\Helpers\FriendsHelper;

class FriendsController extends Controller
{
    public function get_user_friends(Request $request, User $user) {
        $friends_per_page = 10;
        $current_page = max(1, (int) $request->query('page', 1));
        $offset = ($current_page - 1) * $friends_per_page;
        $user_id = $user->id;

        $countSql = <<<SQL
            SELECT COUNT(*) AS total
            FROM friends f
            WHERE ? = ANY(f.friends);
        SQL;

        $total = (int) (DB::selectOne($countSql, [$user_id])->total ?? 0);

        $sql = <<<SQL
            SELECT JSON_BUILD_OBJECT(
                'id', friends_list.friend_user_id,
                'name', u.name,
                'avatar', upr.avatar
            ) AS friend_item
            FROM (
                SELECT
                    CASE
                        WHEN f.friends[1] = ? THEN f.friends[2]
                        WHEN f.friends[2] = ? THEN f.friends[1]
                    END AS friend_user_id
                FROM friends f
                WHERE ? = ANY(f.friends)
                ORDER BY 1
                LIMIT ?
                OFFSET ?
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

        $friendRows = DB::select($sql, [$user_id, $user_id, $user_id, $friends_per_page, $offset]);
        $friendsPayload = array_map(static fn ($row) => json_decode($row->friend_item, true), $friendRows);

        return [
            'user_friends' => $friendsPayload,
            'pagination' => [
                'page' => $current_page,
                'friends_per_page' => $friends_per_page,
                'total' => $total,
                'last_page' => max(1, (int) ceil($total / $friends_per_page)),
            ],
        ];
    }

    public function add_friend(User $friend) {
        $friend_status = FriendsHelper::check_friend_status_with_this_user($friend);
        if ($friend_status !== 'not_friends') {
            abort(400);
        }

        $user = auth()->user();
        if ($user == null) {
            abort(401);
        }

        $friend_request = [
            'requester_id' => $user->id,
            'recipient_id' => $friend->id,
            'status' => 'pending',
        ];

        FriendsRequest::create($friend_request);

        return __('Friend request created successfully');
    }

    public function cancel_friends_request(User $friend) {
        $friend_status = FriendsHelper::check_friend_status_with_this_user($friend);
        if ($friend_status !== 'your_friend_request_is_pending') {
            abort(400);
        }

        $user = auth()->user();
        if ($user == null) {
            abort(401);
        }

        $friends_request = FriendsRequest::where('requester_id', $user->id)
        ->where('recipient_id', $friend->id)
        ->where('status', 'pending')
        ->orderBy('id', 'desc')->first();

        $friends_request->delete();

        return __('Friend request deleted');
    }

    public function accept_friend_request(User $friend) {
        $friend_status = FriendsHelper::check_friend_status_with_this_user($friend);
        if ($friend_status !== 'this_user_wants_add_you_to_friends') {
            abort(400);
        }

        $user = auth()->user();
        if ($user == null) {
            abort(401);
        }

        $friends_request = FriendsRequest::where('requester_id', $friend->id)
        ->where('recipient_id',  $user->id)
        ->where('status', 'pending')
        ->orderBy('id', 'desc')->first();

        $friends_request->update([
            //'status' => 'accepted',
        ]);
        
        $sql = <<<SQL
        INSERT INTO friends (friends) VALUES (ARRAY[?::BIGINT, ?::BIGINT]);
        SQL;

        DB::statement($sql, [$user->id, $friend->id]);

        return __('Friend request accepted');
    }
}

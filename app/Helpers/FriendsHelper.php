<?php
namespace App\Helpers;

use App\Models\FriendsRequest;
use App\Models\User;

use Illuminate\Support\Facades\DB;

class FriendsHelper {
    public static function check_friend_status_with_this_user(User $friend) {
        $user = auth()->user();

        if ($user == null) {
            return 'unauthorized';
        }

        if ($user->id === $friend->id) {
            return 'same_user';
        }
        
        $sql = <<<SQL
        SELECT * FROM friends 
        WHERE normalize_friends(friends) = normalize_friends (ARRAY[?::BIGINT, ?::BIGINT]);
        SQL;

        $friends = DB::select($sql, [$user->id, $friend->id]);

        if (count($friends) > 0) {
            return 'already_friends';
        }

        $fr1 = FriendsRequest::where('requester_id', $user->id)
        ->where('recipient_id', $friend->id)
        ->whereNot('status', 'accepted')
        ->orderBy('id', 'desc')->first();

        $fr2 = FriendsRequest::where('requester_id', $friend->id)
        ->where('recipient_id', $user->id)
        ->whereNot('status', 'accepted')
        ->orderBy('id', 'desc')->first();

        if ($fr1 == null && $fr2 == null) {
            return 'not_friends';
        }

        if ($fr1 != null && $fr1->status === 'pending') {
            return 'your_friend_request_is_pending';
        }

        if ($fr2 != null && $fr2->status === 'pending') {
            return 'this_user_wants_add_you_to_friends';
        }

        if ($fr1 != null && $fr1->status === 'declined') {
            return 'your_cannot_add_this_user_to_friends';
        }

        if ($fr2 != null && $fr2->status === 'declined') {
            return 'you_declined_friend_request_from_this_user';
        }

        if (config('app.debug') == true) {
             dd('Error in FriendHelper::check_friend_status_with_this_user()');
        }

        abort(500);
    }

}
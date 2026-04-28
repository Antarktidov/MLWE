<?php
namespace App\Helpers;

use App\Models\FriendsRequest;

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
        WHERE normalize_friends(friends) = normalize_friends (ARRAY[?, ?]);
        SQL;

        if (DB::select($sql, [$user->id, $friend->id]) != null) {
            return 'already_friends';
        }

        $fr1 = FriendsRequest::where('requester_id', $user->id)
        ->where('recipient_id', $friend->id)
        ->last();
    }

}
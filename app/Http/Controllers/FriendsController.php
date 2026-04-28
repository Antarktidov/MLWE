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
            SELECT ARRAY_AGG(
                CASE 
                    WHEN friends[1] = ? THEN friends[2]
                    WHEN friends[2] = ? THEN friends[1]
                END
            ) AS user_friends
            FROM friends 
            WHERE ? = ANY(friends);
        SQL;
        return [
            'user_friends'=> $friends,
        ];
        //$friends = DB::select('SELECT friends FROM friends WHERE ? = ANY(friends);', [$user->id]);
        //return $friends;
    }
}

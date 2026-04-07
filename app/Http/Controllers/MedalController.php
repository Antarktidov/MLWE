<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserMedal;
use App\Models\Medal;

class MedalController extends Controller
{
    public function give(Request $request, User $user) {
        $data = request()->validate([
            'medal' => 'int',
        ]);
        
        $medal_id = $data['medal'];
        $giver = auth()->user();

        $user_medal = [
            'medal_id' => $medal_id,
            'giver_id' => $giver->id,
            'user_id' => $user->id,
            'wiki_id' => 0,
        ];

        $user_medal2 = UserMedal::where('medal_id', $user_medal['medal_id'])
        ->where('giver_id', $user_medal['giver_id'])
        ->where('user_id', $user_medal['user_id'])
        ->where('wiki_id', $user_medal['wiki_id'])
        ->first();

        if ($user_medal2 != null) {
            return 'У участника уже есть такая медаль';
        }

        UserMedal::create($user_medal);
        return 'Медаль выдана';
    }

    public function take_away(Request $request, User $user, Medal $medal) {
        $medal_id = $medal->id;
        $giver = auth()->user();

        $user_medal = [
            'medal_id' => $medal_id,
            'giver_id' => $giver->id,
            'user_id' => $user->id,
            'wiki_id' => 0,
        ];

        $user_medal2 = UserMedal::where('medal_id', $user_medal['medal_id'])
        ->where('giver_id', $user_medal['giver_id'])
        ->where('user_id', $user_medal['user_id'])
        ->where('wiki_id', $user_medal['wiki_id'])
        ->first()
        ->delete();

        return 'Медаль отобрана';
    }
}

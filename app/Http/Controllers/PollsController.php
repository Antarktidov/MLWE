<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Poll;
use App\Models\PollVote;
use Illuminate\Support\Facades\DB;

class PollsController extends Controller
{
    public function create() {
        return view('create-poll');
    }

    public function store(Request $request) {
        $data = $request->validate([
            'title' => 'required|string',
            'variants' => 'required|array|min:2',
        ]);

        $user = auth()->user();

        $variantsArray = is_array($data['variants']) 
                ? $data['variants']
                : json_decode($data['variants'], true);

        $sql = <<<SQL
        INSERT INTO polls (title, user_id, variants) VALUES (?, ?, ?::text[])
        SQL;

        DB::statement($sql, [$data['title'], $user->id, 
                                            '{' . implode(',', array_map(function($item) {
                                                return '"' . addslashes($item) . '"';
                                            }, $variantsArray)) . '}'
                                            ]);

        return __('Poll created successfully');
    }

    public function accept_vote(Request $request, Poll $poll) {
        $data = $request->validate([
            'variant_idx' => 'required|integer',
        ]);

        $user = auth()->user();
        if ($user == null) {
            abort(401);
        }

        $poll_vote = [
            'user_id' => $user->id,
            'variant_idx' => $data['variant_idx'],
            'poll_id' => $poll->id,
        ];

        $poll_vote2 = PollVote::where('user_id', $poll_vote['user_id'])
        ->where('poll_id', $poll_vote['poll_id'])
        ->first();

        if ($poll_vote2 != null) {
            return response('Вы уже проголосовали', 422)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        PollVote::create($poll_vote);

        return 'Голос сохранён';
    }
}

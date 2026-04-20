<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Poll;
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

        return __('Poll create successfully');
    }
}

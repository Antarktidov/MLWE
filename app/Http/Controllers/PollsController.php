<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

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

        dd($data);
    }
}

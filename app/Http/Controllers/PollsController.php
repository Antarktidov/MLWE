<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PollsController extends Controller
{
    public function create() {
        return view('create-poll');
    }
}

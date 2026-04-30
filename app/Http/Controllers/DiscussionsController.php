<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DiscussionsController extends Controller
{
    public function show() {
        return inertia('discussions-show');
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Wiki;
use App\Models\DiscussionPost;
use App\Models\DiscussionPostRevision;
use App\Models\DiscussionPostLike;
use App\Models\DiscussionCategory;

class DiscussionsController extends Controller
{
    public function index(string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        $posts = DiscussionPost::whereNull('deleted_at')
        ->where('wiki_id', $wiki->id)
        ->orderBy('created_at', 'desc')
        ->select(['id', 'user_id', 'title', 'created_at', 'category_id', 'type'])
        ->paginate(10);
        //$revision = DiscussionPostRevision
        return inertia('discussions-show');
    }
}

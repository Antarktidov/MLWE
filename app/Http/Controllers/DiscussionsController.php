<?php

namespace App\Http\Controllers;

use App\Models\Wiki;
use App\Models\DiscussionPost;
use App\Models\DiscussionCategory;

class DiscussionsController extends Controller
{
    public function index(string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        abort_if($wiki === null, 404);

        $posts = DiscussionPost::whereNull('deleted_at')
            ->where('wiki_id', $wiki->id)
            ->orderBy('created_at', 'desc')
            ->select(['id', 'user_id', 'title', 'created_at', 'category_id', 'type'])
            ->paginate(10);

        dd($posts);
    }

    public function html_index(string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();

        abort_if($wiki === null, 404);

        $wikiId = $wiki->id;

        $user = auth()->user();
        if ($user != null) {
            $userId = $user->id;
            $userName = $user->name;
            $userCanModerateDiscussions = $user->can('moderate-discussions');
        } else {
            $userId = 0;
            $userName = null;
            $userCanModerateDiscussions = false;
        }

        return view('discussions-index', compact([
            'userId', 'userName', 'userCanModerateDiscussions',
            'wiki', 'wikiId'
        ]));
    }

    public function get_all_discussions_categories() {
        $categories = DiscussionCategory::all()
        ->select(['id', 'name']);

        return $categories;
    }
}

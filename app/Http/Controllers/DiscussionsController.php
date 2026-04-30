<?php

namespace App\Http\Controllers;

use App\Models\Wiki;
use App\Models\DiscussionPost;
use App\Models\DiscussionPostRevision;
use App\Models\DiscussionCategory;

use Illuminate\Http\Request;

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

    public function store(Request $request, string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        $data = $request->validate([
            'title' => 'string',
            'content' => 'string',
            'category_id' => 'integer'
        ]);

        $user = auth()->user();
        if ($user != null) {
            $user_id = $user->id;
        } else {
            $user_id = 0;
        }

        $user_ip = $request->ip();

        $post = [
            'title' => $data['title'],
            'category_id' => $data['category_id'],
            'type' => 'post',
            'author_id' => $user_id,
            'author_ip' => $user_ip,
        ];
        $created_post = DiscussionPost::create($post);

        $post_revision = [
            'title' => $data['title'],
            'content' => $data['content'],
            'author_ip' => $user_ip,
            'post_id' => $created_post->id,
        ];
        DiscussionPostRevision::create($post_revision);

        return ["Post created!"];
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

    public function get_all_discussions_categories(string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        abort_if($wiki === null, 404);

        $categories = DiscussionCategory::where('wiki_id', $wiki->id)
        ->select(['id', 'name'])
        ->get();

        return $categories;
    }
}

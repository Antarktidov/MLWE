<?php

namespace App\Http\Controllers;

use App\Models\Wiki;
use App\Models\DiscussionPost;
use Inertia\Inertia;

class DiscussionsController extends Controller
{
    public function index() {
        $wiki = Wiki::first();
        abort_if($wiki === null, 404);

        $posts = DiscussionPost::whereNull('deleted_at')
            ->where('wiki_id', $wiki->id)
            ->orderBy('created_at', 'desc')
            ->select(['id', 'user_id', 'title', 'created_at', 'category_id', 'type'])
            ->paginate(10);

            $posts = null;

        return inertia('discussions-show', [
            'wiki' => [
                'id' => $wiki->id,
                'url' => $wiki->url,
            ],
            'posts' => $posts,
        ]);
        
    }
}

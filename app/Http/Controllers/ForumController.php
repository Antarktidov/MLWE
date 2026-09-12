<?php

namespace App\Http\Controllers;

use App\Models\Wiki;
use App\Models\Article;

use Illuminate\Http\Request;

class ForumController extends Controller
{
    //Форма создания темы форума
    public function create(string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            return view('create-topic', compact('wiki'));
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //POST-ручка для создания статьи
    public function store(string $wikiName, Request $request) {
        $data = request()->validate([
            'title' => 'string',
        ]);

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $my_topic = [
                'wiki_id' => $wiki->id,
                'title' => $data['title'],
                'url_title' => $data['title'],
                'namespace' => 'forum',
            ];
            $created_topic = Article::create($my_topic);

            dd("Zaglushka foruma");
            #return redirect()->route('articles.show', [$wiki->url, $created_article->url_title]);

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }
}

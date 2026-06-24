<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Revision;
use App\Models\Wiki;
use App\Models\Option;
use App\Models\Image;
use App\Models\Quiz;
use App\Models\Poll;
use App\Models\PollVote;
use App\Models\Category;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class ArticleController extends Controller
{
    //Заглавная конкретной вики: список всех статей
    //(Аналог Служебная:Все страницы)
    public function index(string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $user = auth()->user();
            if ($wiki) {
                if ($user != null) {
                    $can_check_revisions = $user->can('check_revisions', $wiki->url);
                    $userCanApproveComments = $user->can('check_comments', $wiki->url);
                } else {
                    $can_check_revisions = false;
                    $userCanApproveComments = false;
                }
            }
            if ($can_check_revisions) {
                $articles = Article::where('wiki_id', $wiki->id)->whereNull('deleted_at')->get();
            } else {
                if ($user != null) {
                    $articles = DB::table('articles')
                    ->select('articles.*')
                    ->where('articles.wiki_id', $wiki->id)
                    ->whereNull('articles.deleted_at')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('revisions')
                            ->whereColumn('revisions.article_id', 'articles.id')
                            ->where('revisions.is_approved', true);
                    })
                    ->get();
                } else {
                    $articles = DB::table('articles')
                    ->select('articles.*')
                    ->where('articles.wiki_id', $wiki->id)
                    ->whereNull('articles.deleted_at')
                    ->whereExists(function ($query) {
                        $query->select(DB::raw(1))
                            ->from('revisions')
                            ->whereColumn('revisions.article_id', 'articles.id')
                            ->where('revisions.is_approved', true)
                            ->where('revisions.is_patrolled', true);
                    })
                    ->get();
                }
            }

            return view('show-all-articles', compact('articles', 'wiki'));
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //Показывает вики-страницу
    public function show(string $wikiName, string $articleName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        $user = auth()->user();
        if ($wiki) {
            $article = Article::where('wiki_id', $wiki->id)
                ->whereNull('deleted_at')
                ->where('url_title', $articleName)
                ->first();
            if($article) {

                $categories_ids = explode(',', substr($article->categories_ids, 1, -1));
                $categories = Category::findMany($categories_ids);
                //dd($categories);

                $userAlreadyVotedInPull = false;

                if ($user != null) {
                    $can_check_revisions = $user->can('check_revisions', $wiki->url);
                    $userCanApproveComments = $user->can('check_comments', $wiki->url);

                    if ($article->poll_id !== 0) {
                        $pv = PollVote::where('user_id', $user->id)
                        ->where('poll_id', $article->poll_id)
                        ->first();

                        if ($pv != null) {
                            $userCanVoteInPoll = false;
                            $userAlreadyVotedInPull = true;
                        } else {
                            $userCanVoteInPoll = true;
                        }
                    } else {
                        $userCanVoteInPoll = false;
                    }

                } else {
                    $can_check_revisions = false;
                    $userCanApproveComments = false;
                    $userCanVoteInPoll = false;
                }

                    if ($article->trivia_id !== 0) {
                        $trivia = Quiz::find($article->trivia_id);
                    } else {
                        $trivia = null;
                    }

                    if ($article->poll_id !== 0) {
                        $poll = Poll::find($article->poll_id);
                        $poll['variants']= explode(',', substr($poll->variants, 1, -1));
                    } else {
                        $poll = null;
                    }

                    if ($can_check_revisions) {
                        $revision = Revision::where('article_id', $article->id)
                        //->where('deleted_at', '')
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'desc')->first();
                    } else {
                        if ($user != null) {
                            $revision = Revision::where('article_id', $article->id)
                            //->where('deleted_at', '')
                            ->whereNull('deleted_at')
                            ->where('is_approved', true)
                            ->orderBy('id', 'desc')->first();
                        } else {
                            $revision = Revision::where('article_id', $article->id)
                            //->where('deleted_at', '')
                            ->whereNull('deleted_at')
                            ->where('is_approved', true)
                            ->where('is_patrolled', true)
                            ->orderBy('id', 'desc')->first();
                        }
                    }
                    
                    //$user = auth()->user();

                    if ($user != null) {
                        $userId = $user->id;
                        $userName = $user->name;
                        $userCanDeleteComments = $user->can('delete_comments', $wiki->url);
                    } else {
                        $userId = 0;
                        $userName = 'Анонимный участник';
                        $userCanDeleteComments = false;
                    }

                    if ($revision) {
                        $options = Option::getOptions();
                        $images = Image::where('is_approved', true)
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'desc')
                        ->limit(5)
                        ->get();
                        
                        $is_comments_enabled = $options->is_comments_enabled;

                        return view('article', compact('revision', 'wiki', 'article',
                        'userId', 'userName', 'userCanDeleteComments',
                        'userCanApproveComments', 'is_comments_enabled',
                        'images', 'trivia', 'poll', 'userCanVoteInPoll',
                        'userAlreadyVotedInPull', 'categories'));
                    } else {
                        return response(__('Article does not exist'), 404)
                            ->header('Content-Type', 'text/plain');
                    }
            } else {
                return response(__('Article does not exist'), 404)
                    ->header('Content-Type', 'text/plain');
            }
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //Форма создания статьи
    public function create(string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            return view('create-article', compact('wiki'));
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //POST-ручка для создания статьи
    public function store(string $wikiName, Request $request) {
        //dd('test0');
        $data = request()->validate([
            'title' => 'string',
            'url_title' => 'string',
            'content' => 'string',
        ]);

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            //dd(auth()->user());
            $my_article = [
                'wiki_id' => $wiki->id,
                'url_title' => $data['url_title'],
                'title' => $data['title'],
            ];
            $created_article = Article::create($my_article);

            if (auth()->user() != null) {
                $user_id = auth()->user()->id;
            } else {
                $user_id = 0;
            }
            $user_ip = $request->ip();
            $my_revision = [
                'article_id' => $created_article->id,
                'title' =>  $data['title'],
                'url_title' => $data['url_title'],
                'content' => $data['content'],
                'user_id' => $user_id,
                'user_ip' => $user_ip,
            ];

            Revision::create($my_revision);

            return redirect()->route('articles.show', [$wiki->url, $created_article->url_title]);

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //Форма правки статьи
    public function edit(string $wikiName, string $articleName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $article = Article::where('wiki_id', $wiki->id)
                ->whereNull('deleted_at')
                ->where('url_title', $articleName)
                ->first();
            if ($article) {

                $user = auth()->user();

                if ($user != null) {
                    $can_check_revisions = $user->can('check_revisions', $wiki->url);
                } else {
                    $can_check_revisions = false;
                }

                if ($can_check_revisions) {
                    $revision = Revision::where('article_id', $article->id)
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->first();
                } else {
                    $revision = Revision::where('article_id', $article->id)
                    ->whereNull('deleted_at')
                    ->where('is_approved', true)
                    ->orderBy('id', 'desc')
                    ->first();
                }

                if ($revision) {
                    return view('edit', compact('article', 'revision', 'wiki'));
                } else {
                    return response(__('Article does not exist'), 404)
                        ->header('Content-Type', 'text/plain');
                }
            } else {
                return response(__('Article does not exist'), 404)
                    ->header('Content-Type', 'text/plain');
            }
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //POST-ручка для формы правки статьи
    public function update($wikiName, $articleName, Request $request)
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->firstOrFail();
        $user = auth()->user();

        $can_manage_trivia = $user?->can('manage_trivia', $wiki->url) ?? false;
        $can_manage_polls  = $user?->can('manage_polls',  $wiki->url) ?? false;

        // Базовые правила
        $rules = [
            'title'     => 'string',
            'url_title' => 'string',
            'content'   => 'string',
        ];

        // Добавляем поля по правам
        if ($can_manage_trivia) $rules['trivia_id'] = 'integer';
        if ($can_manage_polls)  $rules['poll_id']   = 'integer';

        $data = $request->validate($rules);

        // Формируем массив для обновления
        $my_article = [
            'wiki_id'   => $wiki->id,
            'url_title' => $data['url_title'],
            'title'     => $data['title'],
        ];

        if ($can_manage_trivia) $my_article['trivia_id'] = $data['trivia_id'];
        if ($can_manage_polls)  $my_article['poll_id']   = $data['poll_id'];

        $article = Article::where('wiki_id', $wiki->id)
            ->whereNull('deleted_at')
            ->where('url_title', $articleName)
            ->firstOrFail();

        $article->update($my_article);

        Revision::create([
            'article_id' => $article->id,
            'title'      => $data['title'],
            'url_title'  => $data['url_title'],
            'content'    => $data['content'],
            'user_id'    => $user?->id ?? 0,
            'user_ip'    => $request->ip(),
        ]);

        return redirect()->route('articles.show', [$wiki->url, $article->url_title]);
    }


    //DELETE-ручка для удаления статьи
    //(требуются технические права)
    public function destroy(string $wikiName, string $articleName): Response
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $my_article2 = Article::where('wiki_id', $wiki->id)
                ->whereNull('deleted_at')
                ->where('url_title', $articleName)
                ->first();

            if ($my_article2) {

                $my_article2->delete();
                return response(__('Article was deleted'), 200)
                    ->header('Content-Type', 'text/plain');
            } else {
                return response(__('Error'), 500)
                    ->header('Content-Type', 'text/plain');
            }

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //Список удалённых статей
    //(требуются технические права)
    public function trash(string $wikiName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $user = auth()->user();
            if ($user != null) {
                $can_check_revisions = $user->can('check_revisions', $wiki->url);
            } else {
                $can_check_revisions = false;
            }

            if ($can_check_revisions) {
                $articles = Article::onlyTrashed()->where('wiki_id', $wiki->id)->get();
            } else {
                $articles = DB::table('articles')
                ->select('articles.*')
                ->where('articles.wiki_id', $wiki->id)
                ->whereNotNull('articles.deleted_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('revisions')
                        ->whereColumn('revisions.article_id', 'articles.id')
                        ->where('revisions.is_approved', true);
                })
                ->get();
            }

            return view('trash', compact('articles', 'wiki'));
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //Просмотр удалённой статьи
    //(требуются технические права)
    public function show_deleted(string $wikiName, string $articleName) {
        $wiki = Wiki::where('url', $wikiName)->first();
        if ($wiki) {
            $user = auth()->user();
            if ($user != null) {
                $can_check_revisions = $user->can('check_revisions', $wiki->url);
            } else {
                $can_check_revisions = false;
            }
            $article = Article::onlyTrashed()
                ->where('wiki_id', $wiki->id)
                ->where('url_title', $articleName)
                ->first();

            if($article) {

                    if ($can_check_revisions) {
                        $revision = Revision::where('article_id', $article->id)
                        ->whereNull('deleted_at')
                        ->orderBy('id', 'desc')
                        ->first();
                    } else {
                        $revision = Revision::where('article_id', $article->id)
                        ->whereNull('deleted_at')
                        ->where('is_approved', true)
                        ->orderBy('id', 'desc')
                        ->first();
                    }

                    if ($revision) {
                        return view('deleted-article', compact('revision', 'wiki', 'article'));
                    }

                    else {
                        return response(__('Article does not exist'), 404)
                        ->header('Content-Type', 'text/plain');
                    }
            } else {
                return response(__('Article does not exist'), 404)
                    ->header('Content-Type', 'text/plain');
            }
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    //POST-ручка для восстановления стаьи
    //(требуются технические права)
    public function restore(string $wikiName, string $articleName): Response {
        $wiki = Wiki::where('url', $wikiName)->first();
        if ($wiki) {
            $my_article2 = Article::onlyTrashed()
                ->where('wiki_id', $wiki->id)
                ->where('url_title', $articleName)
                ->first();
            if ($my_article2) {

                $my_article2->restore();
                return response(__('Article was restored'), 200)
                    ->header('Content-Type', 'text/plain');
            } else {
                return response(__('Error'), 500)
                    ->header('Content-Type', 'text/plain');
            }

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function transfer_articles(Article $article) {
        $original_wiki = Wiki::find($article->wiki_id);
        $all_wikis = Wiki::all();
        return view('transfer-articles', compact('original_wiki', 'article', 'all_wikis'));
    }

    public function transfer_articles_post(Article $article, Request $request) {
        $data = $request->validate([
            'wiki-to-transfer' => 'integer|required',
        ]);
        $target_wiki_id = $data['wiki-to-transfer'];
        $article->update([
            'wiki_id' => $target_wiki_id,
        ]);
        return 'Статья перемещена на другую вики';
    }

}

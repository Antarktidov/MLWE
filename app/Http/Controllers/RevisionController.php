<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Revision;
use App\Models\User;
use App\Models\Wiki;

class RevisionController extends Controller
{
    public function destroy(string $wikiName, string $articleName, int $revisionId, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $my_article = $this->findPage($wiki, $articleName, $namespace);

            if ($my_article) {
                $my_revision = Revision::where('article_id', $my_article->id)
                    ->where('id', $revisionId)
                    ->whereNull('deleted_at')
                    ->first();

                if ($my_revision) {
                    $my_revision->delete();
                    return response(__('The edit was hidden'), 200)
                    ->header('Content-Type', 'text/plain');
                } else {
                    return response(__('No such revision'), 404)
                        ->header('Content-Type', 'text/plain');
                }

            } else {
                    return response(__('No such article'), 404)
                        ->header('Content-Type', 'text/plain');
            }

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function restore(string $wikiName, string $articleName, int $revisionId, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $my_article = $this->findPage($wiki, $articleName, $namespace);
            if ($my_article) {
                $my_revision = Revision::onlyTrashed()
                    ->where('article_id', $my_article->id)
                    ->where('id', $revisionId)
                    ->first();
                if ($my_revision) {
                    $my_revision->restore();
                    return response(__('The edit has been restored'), 200)
                        ->header('Content-Type', 'text/plain');
                } else {
                    return response(__('Error'), 500)
                        ->header('Content-Type', 'text/plain');
                }
            } else {
                return response(__('Error'), 500)
                    ->header('Content-Type', 'text/plain');
            }

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function view(string $wikiName, string $articleName, int $revisionId, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $article = $this->findPage($wiki, $articleName, $namespace);

            if ($article) {
                $user = auth()->user();
                if ($user != null) {
                    $can_check_revisions = $user->can('check_revisions', $wiki->url);
                } else {
                    $can_check_revisions = false;
                }

                $revisionQuery = Revision::where('article_id', $article->id)
                    ->where('id', $revisionId)
                    ->whereNull('deleted_at');

                if (!$can_check_revisions) {
                    $revisionQuery->where('is_approved', true);
                }

                $revision = $revisionQuery->first();
                if ($revision) {
                    $view = $namespace === 'blog' ? 'blogs.revision' : 'revision';

                    return view($view, compact('revision', 'wiki', 'article'));
                } else {
                    return response(__('404. Invalid edit id entered.'), 404)
                        ->header('Content-Type', 'text/plain');
                }

            } else {
                return response(__('Error'), 500)
                    ->header('Content-Type', 'text/plain');
            }

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function index(string $wikiName, string $articleName, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $article = $this->findPage($wiki, $articleName, $namespace);
            if ($article) {
                $user = auth()->user();
                if ($user != null) {
                    $can_check_revisions = $user->can('check_revisions', $wiki->url);
                } else {
                    $can_check_revisions = false;
                }

                $revisionsQuery = Revision::where('article_id', $article->id)
                    ->whereNull('deleted_at');

                if (!$can_check_revisions) {
                    $revisionsQuery->where('is_approved', true);
                }

                $revisions = $revisionsQuery->get();
                if ($revisions->isNotEmpty()) {
                    $users = User::all();
                    $view = $namespace === 'blog' ? 'blogs.history' : 'history';

                    return view($view, compact('article', 'revisions', 'users', 'wiki'));
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

    public function index_blog(string $wikiName, User $author,  string $articleName, string $namespace = 'blog')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $article = Article::where('wiki_id', $wiki->id)
            ->where('url_title', $articleName)
            ->where('namespace', 'blog')
            ->where('author_id', $author->id)
            ->first();
            if ($article) {
                $user = auth()->user();
                if ($user != null) {
                    $can_check_revisions = $user->can('check_revisions', $wiki->url);
                } else {
                    $can_check_revisions = false;
                }

                $revisionsQuery = Revision::where('article_id', $article->id)
                    ->whereNull('deleted_at');

                if (!$can_check_revisions) {
                    $revisionsQuery->where('is_approved', true);
                }

                $revisions = $revisionsQuery->get();
                if ($revisions->isNotEmpty()) {
                    $users = User::all();
                    $view = $namespace === 'blog' ? 'blogs.history' : 'history';

                    return view($view, compact('article', 'revisions', 'users', 'wiki'));
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

    public function show_deleted_hist(string $wikiName, string $articleName, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $article = $this->findPage($wiki, $articleName, $namespace, onlyTrashed: true);
            if ($article) {
                $user = auth()->user();
                if ($user != null) {
                    $can_check_revisions = $user->can('check_revisions', $wiki->url);
                } else {
                    $can_check_revisions = false;
                }

                $revisionsQuery = Revision::where('article_id', $article->id);
                if ($can_check_revisions) {
                    $revisionsQuery->withTrashed();
                } else {
                    $revisionsQuery
                        ->whereNull('deleted_at')
                        ->where('is_approved', true);
                }

                $revisions = $revisionsQuery->get();
                if ($revisions->isNotEmpty()) {
                    $users = User::all();
                    $view = $namespace === 'blog' ? 'blogs.deleted_page_history' : 'show_deleted_article_history';

                    return view($view, compact('article', 'revisions', 'users', 'wiki'));
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

    public function trash(string $wikiName, string $articleName, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $article = $this->findPage($wiki, $articleName, $namespace);
            if ($article) {
                $revisions = Revision::onlyTrashed()->where('article_id', $article->id)->get();
                $users = User::all();
                $view = $namespace === 'blog' ? 'blogs.deleted_history' : 'deleted_history';

                return view($view, compact('article', 'revisions', 'users', 'wiki'));
            } else {
                return response(__('Article does not exist'), 404)
                    ->header('Content-Type', 'text/plain');
            }
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function approve(string $wikiName, string $articleName, int $revisionId, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $my_article = $this->findPage($wiki, $articleName, $namespace);
            if ($my_article) {
                $my_revision = Revision::where('article_id', $my_article->id)
                    ->where('id', $revisionId)
                    ->whereNull('deleted_at')
                    ->first();
                if ($my_revision) {
                    $my_revision->update([
                        'is_approved' => true,
                    ]);
                    return response(__('The edit has been approved'), 200)
                        ->header('Content-Type', 'text/plain');
                } else {
                    return response(__('Error'), 500)
                        ->header('Content-Type', 'text/plain');
                }
            } else {
                return response(__('Error'), 500)
                    ->header('Content-Type', 'text/plain');
            }

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function patrol(string $wikiName, string $articleName, int $revisionId, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $my_article = $this->findPage($wiki, $articleName, $namespace);
            if ($my_article) {
                $my_revision = Revision::where('article_id', $my_article->id)
                    ->where('id', $revisionId)
                    ->whereNull('deleted_at')
                    ->first();
                if ($my_revision) {
                    $my_revision->update([
                        'is_patrolled' => true,
                    ]);
                    return response(__('The edit has been patrolled'), 200)
                        ->header('Content-Type', 'text/plain');
                } else {
                    return response(__('Error'), 500)
                        ->header('Content-Type', 'text/plain');
                }
            } else {
                return response(__('Error'), 500)
                    ->header('Content-Type', 'text/plain');
            }

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function depatrol(string $wikiName, string $articleName, int $revisionId, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if ($wiki) {
            $my_article = $this->findPage($wiki, $articleName, $namespace);
            if ($my_article) {
                $my_revision = Revision::where('article_id', $my_article->id)
                    ->where('id', $revisionId)
                    ->whereNull('deleted_at')
                    ->first();
                if ($my_revision) {
                    $my_revision->update([
                        'is_patrolled' => false,
                    ]);
                    return response(__('The edit has been depatrolled'), 200)
                        ->header('Content-Type', 'text/plain');
                } else {
                    return response(__('Error'), 500)
                        ->header('Content-Type', 'text/plain');
                }
            } else {
                return response(__('Error'), 500)
                    ->header('Content-Type', 'text/plain');
            }

        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    private function findPage(Wiki $wiki, string $articleName, string $namespace, bool $onlyTrashed = false): ?Article
    {
        $query = Article::where('wiki_id', $wiki->id)
            ->where('url_title', $articleName)
            ->where('namespace', $namespace);

        if ($onlyTrashed) {
            $query->onlyTrashed();
        } else {
            $query->whereNull('deleted_at');
        }

        return $query->first();
    }
}

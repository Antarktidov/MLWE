<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\Revision;
use App\Models\Wiki;
use App\Models\User;
use App\Models\Option;
use App\Models\Image;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

use App\Services\PermissionService;
use App\Services\RevisionService;
use App\Services\PollService;
use App\Services\CategoryService;
use App\Services\UserService;
use App\Services\TriviaService;

class BlogController extends Controller
{
    private const NS = 'blog';

    public function __construct(
        private PermissionService $permissionService,
        private RevisionService $revisionService,
        private PollService $pollService,
        private CategoryService $categoryService,
        private UserService $userService,
        private TriviaService $triviaService,
    ) {}

    public function index(string $wikiName)
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $user = auth()->user();
        $can_check_revisions = $user?->can('check_revisions', $wiki->url) ?? false;

        if ($can_check_revisions) {
            $articles = Article::where('wiki_id', $wiki->id)
                ->where('namespace', self::NS)
                ->whereNull('deleted_at')
                ->get();
        } elseif ($user != null) {
            $articles = DB::table('articles')
                ->select('articles.*')
                ->where('articles.wiki_id', $wiki->id)
                ->where('articles.namespace', self::NS)
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
                ->where('articles.namespace', self::NS)
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

        return view('blogs.index', compact('articles', 'wiki'));
    }

    public function show(string $wikiName, User $author, string $articleName)
    {
        $wiki = Wiki::active()->byUrl($wikiName)->firstOrFail();
        $article = Article::active()->byWiki($wiki)->byUrl($articleName)
            ->byNS(self::NS)
            ->where('author_id', $author->id)
            ->firstOrFail();

        $user = auth()->user();
        $userId = $user->id ?? 0;
        $userName = $user->name ?? 'Анонимный участник';
        $userCanDeleteComments = $user?->can('delete_comments', $wiki->url) ?? false;
        $userCanApproveComments = $user?->can('check_comments', $wiki->url) ?? false;

        $permissions = $this->permissionService->getArticlePermissions($user, $wiki, $article);
        $revision = $this->revisionService->getVisibleRevision($article, $permissions);
        $poll = $this->pollService->getPollData($article, $user);
        $trivia = $this->triviaService->getTrivia($article);
        $categories = $this->categoryService->getCategories($article);
        $userInfo = $this->userService->getUserInfo($user, $wiki);
        $options = Option::getOptions();
        $is_comments_enabled = $options->is_comments_enabled;
        $images = Image::approved()->latest()->limit(5)->get();

        $canEditBlog = $user && $this->canEditBlog($user, $wiki, $article);
        $author = $article->author_id ? User::find($article->author_id) : null;

        return view('blogs.show', compact(
            'revision', 'wiki', 'article', 'categories',
            'poll', 'trivia', 'permissions', 'userInfo',
            'options', 'images', 'is_comments_enabled',
            'userId', 'userName', 'userCanDeleteComments',
            'userCanApproveComments', 'canEditBlog', 'author'
        ));
    }

    public function create(string $wikiName)
    {
        $user = auth()->user();
        if (!$user) {
            return response('Unauthorized', 401)
                ->header('Content-Type', 'text/plain');
        }

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        return view('blogs.create', compact('wiki'));
    }

    public function store(string $wikiName, Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response('Unauthorized', 401)
                ->header('Content-Type', 'text/plain');
        }

        $data = request()->validate([
            'title' => 'string',
            'url_title' => 'string',
            'content' => 'string',
        ]);

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $created_article = Article::create([
            'wiki_id' => $wiki->id,
            'url_title' => $data['url_title'],
            'title' => $data['title'],
            'namespace' => self::NS,
            'author_id' => $user->id,
        ]);

        Revision::create([
            'article_id' => $created_article->id,
            'title' => $data['title'],
            'url_title' => $data['url_title'],
            'content' => $data['content'],
            'user_id' => $user->id,
            'user_ip' => $request->ip(),
            'is_patrolled' => true,
        ]);

        return redirect()->route('blogs.show', [$wiki->url, $user->id, $created_article->url_title]);
    }

    public function edit(string $wikiName, User $author, string $articleName)
    {
        $user = auth()->user();
        if (!$user) {
            return response('Unauthorized', 401)
                ->header('Content-Type', 'text/plain');
        }

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $article = Article::where('wiki_id', $wiki->id)
            ->whereNull('deleted_at')
            ->where('url_title', $articleName)
            ->where('namespace', self::NS)
            ->where('author_id', $author->id)
            ->first();

        if (!$article) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        if (!$this->canEditBlog($user, $wiki, $article)) {
            return response('Forbidden', 403)
                ->header('Content-Type', 'text/plain');
        }

        $can_check_revisions = $user->can('check_revisions', $wiki->url);

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

        if (!$revision) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        return view('blogs.edit', compact('article', 'revision', 'wiki', 'author'));
    }

    public function update(string $wikiName, User $author, string $articleName, Request $request)
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->firstOrFail();
        $user = auth()->user();

        if (!$user) {
            return response('Unauthorized', 401)
                ->header('Content-Type', 'text/plain');
        }

        $article = Article::where('wiki_id', $wiki->id)
            ->whereNull('deleted_at')
            ->where('url_title', $articleName)
            ->where('namespace', self::NS)
            ->where('author_id', $author->id)
            ->firstOrFail();

        if (!$this->canEditBlog($user, $wiki, $article)) {
            return response('Forbidden', 403)
                ->header('Content-Type', 'text/plain');
        }

        $can_manage_trivia = $user->can('manage_trivia', $wiki->url);
        $can_manage_polls = $user->can('manage_polls', $wiki->url);

        $rules = [
            'title' => 'string',
            'url_title' => 'string',
            'content' => 'string',
        ];

        if ($can_manage_trivia) {
            $rules['trivia_id'] = 'integer';
        }
        if ($can_manage_polls) {
            $rules['poll_id'] = 'integer';
        }

        $data = $request->validate($rules);

        $my_article = [
            'wiki_id' => $wiki->id,
            'url_title' => $data['url_title'],
            'title' => $data['title'],
        ];

        if ($can_manage_trivia) {
            $my_article['trivia_id'] = $data['trivia_id'];
        }
        if ($can_manage_polls) {
            $my_article['poll_id'] = $data['poll_id'];
        }

        $article->update($my_article);

        Revision::create([
            'article_id' => $article->id,
            'title' => $data['title'],
            'url_title' => $data['url_title'],
            'content' => $data['content'],
            'user_id' => $user->id,
            'user_ip' => $request->ip(),
            'is_patrolled' => true,
        ]);

        return redirect()->route('blogs.show', [$wiki->url, $author->id, $article->url_title]);
    }

    public function destroy(string $wikiName, User $author, string $articleName): Response
    {
        $user = auth()->user();
        if (!$user) {
            return response('Unauthorized', 401)
                ->header('Content-Type', 'text/plain');
        }

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $article = Article::where('wiki_id', $wiki->id)
            ->whereNull('deleted_at')
            ->where('url_title', $articleName)
            ->where('namespace', self::NS)
            ->where('author_id', $author->id)
            ->first();

        if (!$article) {
            return response(__('Error'), 500)
                ->header('Content-Type', 'text/plain');
        }

        if (!$this->canEditBlog($user, $wiki, $article)) {
            return response('Forbidden', 403)
                ->header('Content-Type', 'text/plain');
        }

        $article->delete();

        return response(__('Article was deleted'), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function trash(string $wikiName)
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $user = auth()->user();
        $can_check_revisions = $user?->can('check_revisions', $wiki->url) ?? false;

        if ($can_check_revisions) {
            $articles = Article::onlyTrashed()->where('wiki_id', $wiki->id)
                ->where('namespace', self::NS)
                ->get();
        } else {
            $articles = DB::table('articles')
                ->select('articles.*')
                ->where('articles.wiki_id', $wiki->id)
                ->where('namespace', self::NS)
                ->whereNotNull('articles.deleted_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('revisions')
                        ->whereColumn('revisions.article_id', 'articles.id')
                        ->where('revisions.is_approved', true);
                })
                ->get();
        }

        return view('blogs.trash', compact('articles', 'wiki'));
    }

    public function show_deleted(string $wikiName, User $author, string $articleName)
    {
        $wiki = Wiki::where('url', $wikiName)->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $user = auth()->user();
        $can_check_revisions = $user?->can('check_revisions', $wiki->url) ?? false;

        $article = Article::onlyTrashed()
            ->where('wiki_id', $wiki->id)
            ->where('namespace', self::NS)
            ->where('url_title', $articleName)
            ->where('author_id', $author->id)
            ->first();

        if (!$article) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $author = User::find($article->author_id);

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

        if (!$revision) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $canEditBlog = $user && $this->canEditBlog($user, $wiki, $article);

        return view('blogs.deleted', compact('revision', 'wiki', 'article',
        'author', 'canEditBlog'));
    }

    public function restore(string $wikiName, User $author, string $articleName): Response
    {
        $user = auth()->user();
        if (!$user) {
            return response('Unauthorized', 401)
                ->header('Content-Type', 'text/plain');
        }

        $wiki = Wiki::where('url', $wikiName)->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $article = Article::onlyTrashed()
            ->where('wiki_id', $wiki->id)
            ->where('url_title', $articleName)
            ->where('namespace', self::NS)
            ->where('author_id', $author->id)
            ->first();

        if (!$article) {
            return response(__('Error'), 500)
                ->header('Content-Type', 'text/plain');
        }

        if (!$this->canEditBlog($user, $wiki, $article)) {
            return response('Forbidden', 403)
                ->header('Content-Type', 'text/plain');
        }

        $article->restore();

        return response(__('Article was restored'), 200)
            ->header('Content-Type', 'text/plain');
    }

    private function canEditBlog($user, Wiki $wiki, Article $article): bool
    {
        if ($article->author_id === $user->id) {
            return true;
        }

        return $user->can('edit_other_users_blogs', $wiki->url);
    }

    public function user_blog(string $wikiName, User $author) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $user = auth()->user();
        $can_check_revisions = $user?->can('check_revisions', $wiki->url) ?? false;

        if ($can_check_revisions) {
            $articles = Article::where('wiki_id', $wiki->id)
                ->where('namespace', self::NS)
                ->whereNull('deleted_at')
                ->where('author_id', $author->id)
                ->paginate(5);
        } elseif ($user != null) {
            $articles = DB::table('articles')
                ->select('articles.*')
                ->where('articles.wiki_id', $wiki->id)
                ->where('articles.namespace', self::NS)
                ->where('articles.author_id', $author->id)
                ->whereNull('articles.deleted_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('revisions')
                        ->whereColumn('revisions.article_id', 'articles.id')
                        ->where('revisions.is_approved', true);
                })
                ->paginate(5);
        } else {
            $articles = DB::table('articles')
                ->select('articles.*')
                ->where('articles.wiki_id', $wiki->id)
                ->where('articles.namespace', self::NS)
                ->where('articles.author_id', $author->id)
                ->whereNull('articles.deleted_at')
                ->whereExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('revisions')
                        ->whereColumn('revisions.article_id', 'articles.id')
                        ->where('revisions.is_approved', true)
                        ->where('revisions.is_patrolled', true);
                })
                ->paginate(5);
        }

        dd($articles);

        return view('blogs.index', compact('articles', 'wiki'));
    }
}

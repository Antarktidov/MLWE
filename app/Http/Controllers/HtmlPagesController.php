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

use App\Services\PermissionService;
use App\Services\RevisionService;
use App\Services\PollService;
use App\Services\CategoryService;
use App\Services\UserService;
use App\Services\TriviaService;

class HtmlPagesController extends Controller
{
    private const NS = 'html';

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

        return view('html.index', compact('articles', 'wiki'));
    }

    public function show(string $wikiName, string $articleName)
    {
        $wiki = Wiki::active()->byUrl($wikiName)->firstOrFail();
        $article = Article::active()->byWiki($wiki)->byUrl($articleName)
            ->byNS(self::NS)
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

        $canEditHtmlPages = $user && $user->can('edit_html_pages', $wiki->url);

        return view('html.show', compact(
            'revision', 'wiki', 'article', 'categories',
            'poll', 'trivia', 'permissions', 'userInfo',
            'options', 'images', 'is_comments_enabled',
            'userId', 'userName', 'userCanDeleteComments',
            'userCanApproveComments', 'canEditHtmlPages'
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

        return view('html.create', compact('wiki'));
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
            'author_id' => 0,
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

        return redirect()->route('html.show', [$wiki->url, $created_article->url_title]);
    }

    public function edit(string $wikiName, string $articleName)
    {
        $user = auth()->user();

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $article = Article::where('wiki_id', $wiki->id)
            ->whereNull('deleted_at')
            ->where('url_title', $articleName)
            ->where('namespace', self::NS)
            ->first();

        if (!$article) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        if (!$user->can('edit_html_pages', $wiki->url)) {
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

        return view('html.edit', compact('article', 'revision', 'wiki'));
    }

    public function update(string $wikiName, string $articleName, Request $request)
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
            ->firstOrFail();

        if (!$user->can('edit_html_pages', $wiki->url)){
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

        return redirect()->route('html.show', [$wiki->url, $article->url_title]);
    }

    public function destroy(string $wikiName, string $articleName): Response
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
            ->first();

        if (!$article) {
            return response(__('Error'), 500)
                ->header('Content-Type', 'text/plain');
        }

        if (!$user->can('edit_html_pages', $wiki->url)) {
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

        return view('html.trash', compact('articles', 'wiki'));
    }

    public function show_deleted(string $wikiName, string $articleName)
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
            ->first();

        if (!$article) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
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

        if (!$revision) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $canEditHtmlPages = $user && $user->can('edit_html_pages', $wiki->url);

        return view('html.deleted', compact('revision', 'wiki', 'article',
        'canEditHtmlPages'));
    }

    public function restore(string $wikiName, string $articleName): Response
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
            ->first();

        if (!$article) {
            return response(__('Error'), 500)
                ->header('Content-Type', 'text/plain');
        }

        if (!$user->can('edit_html_pages', $wiki->url)) {
            return response('Forbidden', 403)
                ->header('Content-Type', 'text/plain');
        }

        $article->restore();

        return response(__('Article was restored'), 200)
            ->header('Content-Type', 'text/plain');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Wiki;
use App\Models\Article;

use Illuminate\Http\Request;

use App\Services\PermissionService;
use App\Services\RevisionService;
use App\Services\PollService;
use App\Services\CategoryService;
use App\Services\UserService;
use App\Services\TriviaService;

class ForumController extends Controller
{
    public function __construct(
        private PermissionService $permissionService,
        private RevisionService $revisionService,
        private PollService $pollService,
        private CategoryService $categoryService,
        private UserService $userService,
        private TriviaService $triviaService,
    ) {}

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

    //Показывает топик форума
    public function show(string $wikiName, string $articleName)
    {
        $wiki = Wiki::active()->byUrl($wikiName)->firstOrFail();
        $article = Article::active()->byWiki($wiki)->byUrl($articleName)
        ->byNS('forum')
        ->firstOrFail();

        $user = auth()->user();
        $userId = $user->id ?? 0;
        $userName = $user->name ?? 'Анонимный участник';
        $userCanDeleteComments  = $user?->can('delete_comments',  $wiki->url) ?? false;
        $userCanApproveComments  = $user?->can('check_comments',  $wiki->url) ?? false;

        $permissions = $this->permissionService->getArticlePermissions($user, $wiki, $article);
        $revision = $this->revisionService->getVisibleRevision($article, $permissions);
        $poll = $this->pollService->getPollData($article, $user);
        $trivia = $this->triviaService->getTrivia($article);
        $categories = $this->categoryService->getCategories($article);
        $userInfo = $this->userService->getUserInfo($user, $wiki);

        return view('forum-topic', compact(
            'article'
        ));
    }
}

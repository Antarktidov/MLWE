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
use App\Models\Comment;
use App\Models\CommentRevision;
use App\Models\UserProfileRevision;
use App\Models\User;

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

    //Форма создания категории форума
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
    public function show(string $wikiName, string $articleName, string $namespace = 'forum')
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

        if (!$article) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

            $comments = Comment::whereNull('deleted_at')
                ->where('article_id', $article->id)
                ->where('type', 'forum-message')
                ->whereNull('parent_id')
                ->orderBy('created_at', 'desc')
                ->select(['id', 'user_id', 'created_at', 'parent_id', 'title'])
                ->paginate(10);

            $allComments = Comment::whereNull('deleted_at')
                ->where('article_id', $article->id)
                ->where('type', 'mw-message')
                ->orderBy('created_at')
                ->select(['id', 'user_id', 'created_at', 'parent_id'])
                ->get();

        $output_comments = [];

        $user = auth()->user();
        $check_comments = $user != null && $user->can('check_comments', $wiki->url);

        $commentIds = $allComments->pluck('id');
        $userIds = $allComments->pluck('user_id')->filter()->unique()->values();

        $latestProfileRevisions = UserProfileRevision::whereIn('user_id', $userIds)
            ->whereNull('deleted_at')
            ->where('is_approved', true)
            ->select(['user_id', 'avatar'])
            ->orderBy('id', 'desc')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $usersById = User::whereIn('id', $userIds)
            ->select(['id', 'name'])
            ->get()
            ->map(function (User $user) use ($latestProfileRevisions) {
                $profileRevision = $latestProfileRevisions->get($user->id);
                $user->setAttribute('avatar', $profileRevision?->avatar);

                return $user;
            })
            ->keyBy('id');

        $revisionsQuery = CommentRevision::whereIn('comment_id', $commentIds)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->select(['id', 'comment_id', 'content', 'is_approved']);

        if (!$check_comments) {
            $revisionsQuery->where('is_approved', true);
        }

        $revisionsByCommentId = $revisionsQuery->get()
            ->unique('comment_id')
            ->keyBy('comment_id');

        $commentsById = $allComments->keyBy('id');

        foreach ($comments as $comment) {
            $formattedComment = $this->formatCommentWithChildren(
                $comment,
                $commentsById,
                $usersById,
                $revisionsByCommentId,
            );

            if ($formattedComment !== null) {
                $output_comments[] = $formattedComment;
            }
        }

        $messages = [
            'data' => $output_comments,
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ],
        ];

        dd($messages);

        return view('forum-topic', compact(
            'wiki', 'article'
        ));
    }

    //Форма создания темы форума
    public function create_thread(string $wikiName, string $articleName) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        $article = Article::active()->byWiki($wiki)->byUrl($articleName)
        ->byNS('forum')
        ->firstOrFail();
        if ($wiki) {
            return view('create-thread', compact('wiki', 'article'));
        } else {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function show_comments_under_article(string $wikiName, string $articleName, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $article = $this->findPage($wiki, $articleName, $namespace);

        if (!$article) {
            return response(__('Article does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        if ($namespace === "message_wall") {
            $comments = Comment::whereNull('deleted_at')
                ->where('article_id', $article->id)
                ->where('type', 'mw-message')
                ->whereNull('parent_id')
                ->orderBy('created_at', 'desc')
                ->select(['id', 'user_id', 'created_at', 'parent_id'])
                ->paginate(10);

            $allComments = Comment::whereNull('deleted_at')
                ->where('article_id', $article->id)
                ->where('type', 'mw-message')
                ->orderBy('created_at')
                ->select(['id', 'user_id', 'created_at', 'parent_id'])
                ->get();
        } else {

            $comments = Comment::whereNull('deleted_at')
                ->where('article_id', $article->id)
                ->whereNull('parent_id')
                ->orderBy('created_at', 'desc')
                ->select(['id', 'user_id', 'created_at', 'parent_id'])
                ->paginate(10);

            $allComments = Comment::whereNull('deleted_at')
                ->where('article_id', $article->id)
                ->orderBy('created_at')
                ->select(['id', 'user_id', 'created_at', 'parent_id'])
                ->get();
        }

        $output_comments = [];

        $user = auth()->user();
        $check_comments = $user != null && $user->can('check_comments', $wiki->url);

        $commentIds = $allComments->pluck('id');
        $userIds = $allComments->pluck('user_id')->filter()->unique()->values();

        $latestProfileRevisions = UserProfileRevision::whereIn('user_id', $userIds)
            ->whereNull('deleted_at')
            ->where('is_approved', true)
            ->select(['user_id', 'avatar'])
            ->orderBy('id', 'desc')
            ->get()
            ->unique('user_id')
            ->keyBy('user_id');

        $usersById = User::whereIn('id', $userIds)
            ->select(['id', 'name'])
            ->get()
            ->map(function (User $user) use ($latestProfileRevisions) {
                $profileRevision = $latestProfileRevisions->get($user->id);
                $user->setAttribute('avatar', $profileRevision?->avatar);

                return $user;
            })
            ->keyBy('id');

        $revisionsQuery = CommentRevision::whereIn('comment_id', $commentIds)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')
            ->select(['id', 'comment_id', 'content', 'is_approved']);

        if (!$check_comments) {
            $revisionsQuery->where('is_approved', true);
        }

        $revisionsByCommentId = $revisionsQuery->get()
            ->unique('comment_id')
            ->keyBy('comment_id');

        $commentsById = $allComments->keyBy('id');

        foreach ($comments as $comment) {
            $formattedComment = $this->formatCommentWithChildren(
                $comment,
                $commentsById,
                $usersById,
                $revisionsByCommentId,
            );

            if ($formattedComment !== null) {
                $output_comments[] = $formattedComment;
            }
        }

        return response()->json([
            'data' => $output_comments,
            'meta' => [
                'current_page' => $comments->currentPage(),
                'per_page' => $comments->perPage(),
                'total' => $comments->total(),
                'last_page' => $comments->lastPage(),
            ],
        ]);
    }

    private function formatCommentWithChildren(
        Comment $comment,
        $commentsById,
        $usersById,
        $revisionsByCommentId,
    ): ?array {
        $commentRevision = $revisionsByCommentId->get($comment->id);
        $content = $commentRevision ? $commentRevision->content : null;

        if ($content === null) {
            return null;
        }

        $author = $usersById->get($comment->user_id);
        $userName = $author ? $author->name : __('Anonymous user');

        $children = $commentsById
            ->filter(function (Comment $childComment) use ($comment) {
                return $childComment->parent_id === $comment->id;
            })
            //->sortByDesc('created_at')
            ->values();

        $formattedChildren = [];

        foreach ($children as $childComment) {
            $formattedChild = $this->formatCommentWithChildren(
                $childComment,
                $commentsById,
                $usersById,
                $revisionsByCommentId,
            );

            if ($formattedChild !== null) {
                $formattedChildren[] = $formattedChild;
            }
        }

        return [
            'id' => $comment->id,
            'user_id' => $comment->user_id,
            'parent_id' => $comment->parent_id,
            'user_name' => $userName,
            'avatar' => $author?->getAttribute('avatar'),
            'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i:s') : null,
            'content' => Str::of($content)->markdown([
                'html_input' => 'strip',
            ]),
            'markdown_content' => $content,
            'is_approved' => $commentRevision?->is_approved,
            'children' => $formattedChildren,
        ];
    }


    private function findPage(Wiki $wiki, string $articleName, string $namespace)
    {
        if ($namespace === "message_wall") {
            if (ctype_digit($articleName)) {

                return User::findOrFail((int)$articleName);

            } else {
                abort(400);
            }
        }

        return Article::where('wiki_id', $wiki->id)
            ->where('url_title', $articleName)
            ->where('namespace', $namespace)
            ->whereNull('deleted_at')
            ->first();
    }
}

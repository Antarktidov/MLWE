<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Wiki;
use App\Models\Article;
use App\Models\Comment;
use App\Models\CommentRevision;
use App\Models\UserProfileRevision;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Str;

class CommentsController extends Controller
{
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

        $comments = Comment::whereNull('deleted_at')
            ->where('article_id', $article->id)
            ->orderBy('created_at', 'desc')
            ->select(['id', 'user_id', 'created_at'])
            ->paginate(10);

        $output_comments = [];

        $user = auth()->user();
        $check_comments = $user != null && $user->can('check_comments', $wiki->url);

        $commentIds = $comments->pluck('id');
        $userIds = $comments->pluck('user_id')->filter()->unique()->values();

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

        foreach ($comments as $comment) {
            $comment_revision = $revisionsByCommentId->get($comment->id);
            $content = $comment_revision ? $comment_revision->content : null;

            if ($content == null) {
                continue;
            }

            $author = $usersById->get($comment->user_id);
            $user_name = $author ? $author->name : __('Anonymous user');

            $output_comments[] = [
                'id' => $comment->id,
                'user_id' => $comment->user_id,
                'user_name' => $user_name,
                'avatar' => $author?->getAttribute('avatar'),
                'created_at' => $comment->created_at ? $comment->created_at->format('Y-m-d H:i:s') : null,
                'content' => Str::of($content)->markdown([
                    'html_input' => 'strip',
                ]),
                'markdown_content' => $content,
                'is_approved' => $comment_revision->is_approved,
            ];
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

    public function store(string $wikiName, string $articleName, Request $request, string $namespace = 'article')
    {
        $data = request()->validate([
            'content' => 'string',
        ]);
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();

        if ($wiki) {
            $article = $this->findPage($wiki, $articleName, $namespace);

            if ($article) {
                $user = auth()->user();

                $userId = 0;
                if ($user != null) {
                    $userId = $user->id;
                }

                $comment = [
                    'user_id' => $userId,
                    'user_ip' => $request->ip(),
                    'article_id' => $article->id,
                ];

                $created_comment = Comment::create($comment);

                $comment_revision = [
                    'content' => $data['content'],
                    'user_ip' => $request->ip(),
                    'comment_id' => $created_comment->id,
                ];

                CommentRevision::create($comment_revision);

                return [
                    'message' => 'comment_posted',
                    'id' => $created_comment->id,
                ];
            }
        }
    }

    public function delete(string $wikiName, string $articleName, Comment $comment, string $namespace = 'article')
    {
        $comment->delete();
        return ['message' => 'comment deleted'];
    }

    public function update(string $wikiName, string $articleName, Comment $comment, Request $request, string $namespace = 'article')
    {
        $data = request()->validate([
            'content' => 'string',
        ]);
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();

        if ($wiki) {
            $article = $this->findPage($wiki, $articleName, $namespace);

            if ($article) {
                $user = auth()->user();
                if ($user == null) {
                    return response()->json(['error' => 'forbidden'], 403);
                }

                if ($user->id === $comment->user_id) {
                    $comment_revision = [
                        'content' => $data['content'],
                        'user_ip' => $request->ip(),
                        'comment_id' => $comment->id,
                    ];

                    CommentRevision::create($comment_revision);

                    return response()->json(['message' => 'success']);
                } else {
                    return response()->json(['error' => 'forbidden'], 403);
                }

            } else {
                return response()->json(['error' => 'Article not found'], 404);
            }
        } else {
            return response()->json(['error' => 'Wiki not found'], 404);
        }
    }

    public function approve(string $wikiName, string $articleName, Comment $comment, Request $request, string $namespace = 'article')
    {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();

        if ($wiki) {
            $article = $this->findPage($wiki, $articleName, $namespace);

            if ($article) {
                $comment_revision = CommentRevision::where('comment_id', $comment->id)
                    ->whereNull('deleted_at')
                    ->orderBy('id', 'desc')
                    ->first();

                $comment_revision->update([
                    'is_approved' => true,
                ]);

                return response()->json(['message' => 'success']);
            } else {
                return response()->json(['error' => 'Article not found'], 404);
            }

        } else {
            return response()->json(['error' => 'Wiki not found'], 404);
        }
    }

    private function findPage(Wiki $wiki, string $articleName, string $namespace): ?Article
    {
        return Article::where('wiki_id', $wiki->id)
            ->where('url_title', $articleName)
            ->where('namespace', $namespace)
            ->whereNull('deleted_at')
            ->first();
    }
}

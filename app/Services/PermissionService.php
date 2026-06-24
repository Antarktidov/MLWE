<?php

namespace App\Services;

use App\Models\Wiki;
use App\Models\Article;
use App\Models\User;

class PermissionService {
    public function getArticlePermissions($user, Wiki $wiki, Article $article)
    {
        return (object)[
            'can_check_revisions' => $user?->can('check_revisions', $wiki->url) ?? false,
            'can_approve_comments' => $user?->can('check_comments', $wiki->url) ?? false,
            'can_vote_in_poll' => $this->canVoteInPoll($user, $article),
            'already_voted' => $this->alreadyVoted($user, $article),
        ];
    }

    private function canVoteInPoll($user, Article $article)
    {
        if (!$user || $article->poll_id == 0) return false;
        return !PollVote::where('user_id', $user->id)
            ->where('poll_id', $article->poll_id)
            ->exists();
    }

    private function alreadyVoted($user, Article $article)
    {
        if (!$user || $article->poll_id == 0) return false;
        return PollVote::where('user_id', $user->id)
            ->where('poll_id', $article->poll_id)
            ->exists();
    }

    public function profilePermissions(?User $viewer, User $profileOwner, Wiki $wiki): array
    {
        if (!$viewer) {
            return [
                'can_review' => false,
                'is_my_profile' => false,
                'can_manage_global_medals' => false,
                'can_manage_medals' => false,
            ];
        }

        return [
            'can_review' => $viewer->can('review_user_profiles', $wiki->url),
            'is_my_profile' => $viewer->id === $profileOwner->id,
            'can_manage_global_medals' => $viewer->can('manage_global_medals', $wiki->url),
            'can_manage_medals' => $viewer->can('manage_medals', $wiki->url),
        ];
    }
}

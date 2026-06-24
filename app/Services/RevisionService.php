<?php

namespace App\Services;

use App\Models\Revision;
use App\Models\Article;

class RevisionService {
    public function getVisibleRevision(Article $article, $permissions)
    {
        $q = Revision::where('article_id', $article->id)
            ->whereNull('deleted_at')
            ->orderByDesc('id');

        if ($permissions->can_check_revisions) {
            return $q->first();
        }

        $q->where('is_approved', true);

        if (!auth()->check()) {
            $q->where('is_patrolled', true);
        }

        return $q->first();
    }
}

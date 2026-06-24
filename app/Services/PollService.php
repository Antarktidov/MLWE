<?php

namespace App\Services;

use App\Models\Poll;
use App\Models\Article;

class PollService {
    public function getPollData(Article $article, $user)
    {
        if ($article->poll_id == 0) return null;

        $poll = Poll::find($article->poll_id);
        $poll->variants = explode(',', trim($poll->variants, '[]'));

        return $poll;
    }
}

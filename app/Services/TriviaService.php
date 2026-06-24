<?php

namespace App\Services;

use App\Models\Quiz;
use App\Models\Article;

class TriviaService
{
    public function getTrivia(Article $article)
    {
        if ($article->trivia_id == 0) {
            return null;
        }

        return Quiz::find($article->trivia_id);
    }
}

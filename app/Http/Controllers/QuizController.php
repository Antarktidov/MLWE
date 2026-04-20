<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use Illuminate\Support\Facades\DB;

class QuizController extends Controller
{
    public function create() {
        return view('create-quiz');
    }

    public function store(Request $request) {
        $data = request()->validate([
            'title' => 'required|string',
            'questions' => 'required|array|min:1',
            'questions.*.question' => 'required|string|min:3',
            'questions.*.answer' => 'required|string|min:1',
            'questions.*.wrong_answers' => 'required|array|min:1',
            'questions.*.wrong_answers.*' => 'required|string',
        ]);

        $user = auth()->user();
        $quiz = [
            'title' => $data['title'],
            'user_id' => $user->id,
        ];

        $created_quiz = Quiz::create($quiz);
        $questions = $data['questions'];

        foreach ($questions as $q) {
            // Вариант A: Если $q->wrong_answers уже массив PHP
            //dd($q['wrong_answers']);
            $wrongAnswersArray = is_array($q['wrong_answers']) 
                ? $q['wrong_answers']
                : json_decode($q['wrong_answers'], true);

            $sql = <<<SQL
                    INSERT INTO quiz_questions (question, answer, quiz_id, wrong_answers) VALUES 
                    (?, ?, ?, ?::text[])
                    SQL;

                        DB::statement($sql, [
                            $q['question'], 
                            $q['answer'], 
                            $created_quiz->id, 
                            '{' . implode(',', array_map(function($item) {
                                return '"' . addslashes($item) . '"';
                            }, $wrongAnswersArray)) . '}'
                        ]);
                    }

        return __('Quiz created');
    }
}

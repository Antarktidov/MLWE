<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Quiz;
use App\Models\QuizQuestion;

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
            $q['question_id'] = $created_quiz->id;
        }

        QuizQuestion::insert($questions);

        dd($data);
    }
}

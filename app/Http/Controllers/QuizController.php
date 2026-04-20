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
        $data = $request->validate([
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

        foreach ($data['questions'] as $q) {
            $quiz = [
                'quiz_id' => $created_quiz->id,
                'question' => $q['question'],
                'answer' => $q['answer'],
                'wrong_answers' => $q['wrong_answers'],
            ];
        }

        return redirect()->route('index')->with('success', 'Викторина успешно создана!');
    }
}

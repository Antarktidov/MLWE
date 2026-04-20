@extends('layouts.app')
@section('content')
    <style>
        main {
            display: grid;
            grid-template-columns: 20vw 1fr 20vw;
            margin-right: auto;
            margin-left: auto;
        }

        main .right-column {
            overflow-x: auto;
            width: 18vw;
        }

        .recent-images {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 10px;
            overflow-x: auto;
        }

        .recent-img-item {
            object-fit: cover;
                
            height: 150px;
            width: calc(18vw * 0.9);
            background-size: cover;
            background-position: center;
            flex: 0 0 calc(18vw * 0.9);
        }
        .recent-img-item-wrapper {
            & .time {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }
        }
        .ri-header {
            margin-bottom: 0.5rem;
        }
        .trivia {
            border: 1px solid;
            padding: 10px;
            border-radius: 12px;
        }
        .answer {
            border: 1px solid;
            padding: 10px;
            margin-bottom: 10px;
            border-radius: 12px;
        }
    </style>
    @if ( $trivia != null )
        <script>
                var quizId = {{$trivia->id}};
        </script>
    @endif
    <h1>{{$revision->title}}</h1>
    <div class="links">
    <a href="{{route('articles.edit', [$wiki->url, $article->url_title])}}" class="btn btn-primary">{{__('Edit')}}</a>
    <a href="{{route('articles.history', [$wiki->url, $article->url_title])}}" class="btn btn-secondary">{{__('History')}}</a>
    @can('delete', $wiki->url)
    <form action="{{route('articles.destroy',  [$wiki->url, $article->url_title])}}" method="post">
        @csrf
        @method('delete')
        <button class="btn btn-danger" type="submit">{{__('Delete')}}</button>
    </form>
    @endcan
    </div>
    @if (!$revision->is_patrolled)
    <div class="alert alert-warning mt-3" role="alert">
        {{ __("This revision hasn't been patrolled yet and can contain disinformation") }}
    </div>
    @endif
    <p class="mt-3">{!!Str::of($revision->content)->markdown([
        'html_input' => 'strip',
    ])!!}</p>
    @if ($is_comments_enabled)
    <div id="comments"
         data-wiki-name="{{ $wiki->url }}"
         data-article-name="{{ $article->url_title }}"
         data-user-id="{{ $userId }}"
         data-user-name="{{ $userName }}"
         data-user-can-delete-comments="{{ $userCanDeleteComments ? 'true' : 'false' }}"
         data-user-can-approve-comments="{{ $userCanApproveComments ? 'true' : 'false' }}"
         >
    </div>
    @endif
@endsection
@section('right-column')
    @if (count($images) > 0)
        <div class="ri-header fw-bold">{{ __('Recent images') }}</div>
    @endif
<div class="recent-images">
    @foreach ($images as $img)
    <div class="recent-img-item-wrapper">
        <img class="recent-img-item" src="{{ asset('/storage/images/' . rawurlencode($img->filename)) }}" alt="" loading="lazy">
        <div class="time ms-auto fst-italic text-secondary">{{ $img->updated_at->diffForHumans() }}</div>
    </div>
    @endforeach
</div>
    @if ( $trivia != null )
        <div class="trivia">
            <h4>{{$trivia->title}}</h4>
            <div class="trivia-body m-3">
                <center><button onclick="showQuestion(0)" class="btn btn-outline-warning">{{__('Start')}}</button></center>
            </div>
        </div>
        <script defer>
            var questionIdx = 0;
            var correctAnswersCount = 0;
            var questions;
            var quizBody = document.querySelector('.trivia-body');
        
            var url = `/api/quizes/show/${quizId}`;

            fetch(url, { method: 'GET' })
            .then(Result => Result.json())
            .then(data => {
                //console.log(data);
                questions = data.questions;
                if (questions && questions.length > 0) {
                    //showQuestion(0);
                    //console.log(questions);
                }
            })
            .catch(errorMsg => { console.log(errorMsg); });

            function showQuestion(idx) {
                console.log(questions[idx]);

                if (idx > questions.length - 1) {
                    finishQuiz();
                    return;
                }


                var question = questions[idx];
                var question_title = escapeHTML(question.title);

                var safeAnswer = escapeHTML(question['answer']);
                var wrongAnswers = question.wrong_answers;
                var wrongAnswersArr = [];

                if (Array.isArray(wrongAnswers)) {
                    wrongAnswersArr = wrongAnswers.map(escapeHTML);
                } else if (typeof wrongAnswers === 'string') {
                    try {
                        var parsed = JSON.parse(wrongAnswers.replace(/'/g, '"'));
                        wrongAnswersArr = Array.isArray(parsed) ? parsed.map(escapeHTML) : [];
                    } catch(e) {
                        wrongAnswersArr = wrongAnswers.slice(1, -1).split(',').map(s => 
                            escapeHTML(s.trim().replace(/^['"]|['"]$/g, ''))
                        );
                    }
                }

                console.log(wrongAnswersArr);

                var answerHTML = `<div class="answer" id="answer"></div>`;
                var answersHTMLArr = [answerHTML];

                for (var i = 0; i < wrongAnswersArr.length; i++) {
                    var el = `<div class="answer" id="wrong-answer-${i}"></div>`;
                    answersHTMLArr.push(el);
                }

                shuffle(answersHTMLArr);

                quizBody.innerHTML = '<h5>' + escapeHTML(question.question) + '</h5>';
                quizBody.innerHTML += answersHTMLArr.join("");

                document.querySelectorAll('#answer').forEach(el => {
                    el.textContent = safeAnswer;
                    el.addEventListener('click', right);
                });

                document.querySelectorAll('[id^="wrong-answer-"]').forEach((el, idx) => {
                    if (wrongAnswersArr[idx]) {
                        el.textContent = wrongAnswersArr[idx];
                        el.addEventListener('click', wrong);
                    }
                });

                questionIdx++;
            }
        // Source - https://stackoverflow.com/a/2450976
        // Posted by ChristopheD, modified by community. See post 'Timeline' for change history
        // Retrieved 2026-04-17, License - CC BY-SA 4.0

        function shuffle(array) {
        let currentIndex = array.length;

        // While there remain elements to shuffle...
        while (currentIndex != 0) {

            // Pick a remaining element...
            let randomIndex = Math.floor(Math.random() * currentIndex);
            currentIndex--;

            // And swap it with the current element.
            [array[currentIndex], array[randomIndex]] = [
            array[randomIndex], array[currentIndex]];
            }
        }

        function wrong() {
            showQuestion(questionIdx);
        }
        function right() {
            correctAnswersCount++;
            showQuestion(questionIdx);
        }
        function finishQuiz() {
            quizBody.innerText = `Вы окончили квиз. Количество правильных ответов: ${correctAnswersCount}.`;
        }
        function escapeHTML(str) {
            if (!str) return '';
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }
        </script>
    @endif
@endsection
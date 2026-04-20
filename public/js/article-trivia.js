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
            quizBody.innerHTML = `<div>${escapeHTML('Вы окончили квиз. Количество правильных ответов: '+ correctAnswersCount + '.')}</div>`;
            quizBody.innerHTML += '<center><button class="btn btn-outline-warning mt-3" onclick="restart()">Заново</button></center>'
        }
        function restart() {
            questionIdx = 0;
            correctAnswersCount = 0;
            showQuestion(questionIdx);
        }
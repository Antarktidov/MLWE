@extends('layouts.app')
@section('content')
<style>
    .wrong-answer-input-wrapper {
        display: flex;
        gap: 10px;
        margin-bottom: 10px;
    }
    .wrong-answers-container {
        margin-bottom: 15px;
    }
    .question-container {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 20px;
        border-radius: 5px;
        /*background-color: #f9f9f9;*/
    }
    .question-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    .question-title {
        font-weight: bold;
        font-size: 1.1em;
    }
    .questions-container {
        margin-bottom: 25px;
    }
</style>
<form action="{{ route('quiz.store') }}" method="post">
    @csrf
    <div class="mb-3">
      <label for="title" class="form-label">{{__('Title of trivia quiz')}}</label>
      <input type="text" name="title" class="form-control" id="title">
    </div>
    
    <div class="questions-container" id="questions-container">
        <!-- Первый вопрос -->
        <div class="question-container" data-question-index="0">
            <div class="question-header">
                <div class="question-title">{{__('Question')}} <span class="question-number">1</span></div>
                <button type="button" class="btn btn-danger btn-sm remove-question-btn" style="display: none;">{{__('Remove question')}}</button>
            </div>
            <div class="mb-3">
                <label for="question_0" class="form-label">{{__('Question text')}}</label>
                <input type="text" name="questions[0][question]" class="form-control question-input" id="question_0" placeholder="{{__('Enter question text')}}">
            </div>
            <div class="mb-3">
                <label for="answer_0" class="form-label">{{__('Correct answer')}}</label>
                <input type="text" name="questions[0][answer]" class="form-control answer-input" id="answer_0" placeholder="{{__('Enter correct answer')}}">
            </div>
            <div class="mb-3">
                <label class="form-label">{{__('Wrong answers')}}</label>
                <div class="wrong-answers-container" id="wrong-answers-container-0">
                    <div class="wrong-answer-input-wrapper">
                        <input type="text" name="questions[0][wrong_answers][]" class="form-control wrong-answer-input" placeholder="{{__('Wrong answer')}}">
                        <button type="button" class="btn btn-danger remove-wrong-answer-btn" style="display: none;">{{__('Remove')}}</button>
                    </div>
                </div>
                <button type="button" class="btn btn-warning add-wrong-answer-btn" data-question-index="0">{{__('Add wrong answer')}}</button>
            </div>
        </div>
    </div>
    
    <button type="button" class="btn btn-success" id="add-question-btn">
        {{ __('Add new question') }}
    </button>
    <button type="submit" class="btn btn-primary">
        {{ __('Save quiz') }}
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const questionsContainer = document.getElementById('questions-container');
    const addQuestionBtn = document.getElementById('add-question-btn');
    let questionCounter = 1; // Начинаем с 1, так как первый вопрос уже есть
    
    // Функция для обновления нумерации вопросов
    function updateQuestionNumbers() {
        const questionContainers = questionsContainer.querySelectorAll('.question-container');
        questionContainers.forEach((container, index) => {
            const questionNumberSpan = container.querySelector('.question-number');
            if (questionNumberSpan) {
                questionNumberSpan.textContent = index + 1;
            }
            
            // Обновляем data-question-index
            container.setAttribute('data-question-index', index);
            
            // Обновляем ID и name полей
            const questionInput = container.querySelector('.question-input');
            const answerInput = container.querySelector('.answer-input');
            const wrongAnswersContainer = container.querySelector('.wrong-answers-container');
            const addWrongAnswerBtn = container.querySelector('.add-wrong-answer-btn');
            
            if (questionInput) {
                questionInput.id = `question_${index}`;
                questionInput.name = `questions[${index}][question]`;
                questionInput.previousElementSibling.setAttribute('for', `question_${index}`);
            }
            
            if (answerInput) {
                answerInput.id = `answer_${index}`;
                answerInput.name = `questions[${index}][answer]`;
                answerInput.previousElementSibling.setAttribute('for', `answer_${index}`);
            }
            
            if (wrongAnswersContainer) {
                wrongAnswersContainer.id = `wrong-answers-container-${index}`;
            }
            
            if (addWrongAnswerBtn) {
                addWrongAnswerBtn.setAttribute('data-question-index', index);
            }
            
            // Обновляем name для полей неправильных ответов
            const wrongAnswerInputs = container.querySelectorAll('.wrong-answer-input');
            wrongAnswerInputs.forEach(input => {
                input.name = `questions[${index}][wrong_answers][]`;
            });
        });
        
        // Обновляем видимость кнопок удаления вопросов
        updateRemoveQuestionButtonsVisibility();
    }
    
    // Функция для обновления видимости кнопок удаления вопросов
    function updateRemoveQuestionButtonsVisibility() {
        const questionContainers = questionsContainer.querySelectorAll('.question-container');
        const removeButtons = questionsContainer.querySelectorAll('.remove-question-btn');
        
        // Показываем кнопки удаления только если есть более одного вопроса
        if (questionContainers.length > 1) {
            removeButtons.forEach(btn => btn.style.display = 'inline-block');
        } else {
            removeButtons.forEach(btn => btn.style.display = 'none');
        }
    }
    
    // Функция для добавления нового вопроса
    function addQuestion() {
        const newIndex = questionCounter;
        questionCounter++;
        
        const questionTemplate = `
            <div class="question-container" data-question-index="${newIndex}">
                <div class="question-header">
                    <div class="question-title">{{__('Question')}} <span class="question-number">${newIndex + 1}</span></div>
                    <button type="button" class="btn btn-danger btn-sm remove-question-btn">{{__('Remove question')}}</button>
                </div>
                <div class="mb-3">
                    <label for="question_${newIndex}" class="form-label">{{__('Question text')}}</label>
                    <input type="text" name="questions[${newIndex}][question]" class="form-control question-input" id="question_${newIndex}" placeholder="{{__('Enter question text')}}">
                </div>
                <div class="mb-3">
                    <label for="answer_${newIndex}" class="form-label">{{__('Correct answer')}}</label>
                    <input type="text" name="questions[${newIndex}][answer]" class="form-control answer-input" id="answer_${newIndex}" placeholder="{{__('Enter correct answer')}}">
                </div>
                <div class="mb-3">
                    <label class="form-label">{{__('Wrong answers')}}</label>
                    <div class="wrong-answers-container" id="wrong-answers-container-${newIndex}">
                        <div class="wrong-answer-input-wrapper">
                            <input type="text" name="questions[${newIndex}][wrong_answers][]" class="form-control wrong-answer-input" placeholder="{{__('Wrong answer')}}">
                            <button type="button" class="btn btn-danger remove-wrong-answer-btn" style="display: none;">{{__('Remove')}}</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning add-wrong-answer-btn" data-question-index="${newIndex}">{{__('Add wrong answer')}}</button>
                </div>
            </div>
        `;
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = questionTemplate;
        const newQuestion = tempDiv.firstElementChild;
        
        questionsContainer.appendChild(newQuestion);
        
        // Добавляем обработчики событий для нового вопроса
        setupQuestionEventListeners(newQuestion);
        
        // Обновляем нумерацию
        updateQuestionNumbers();
    }
    
    // Функция для настройки обработчиков событий для вопроса
    function setupQuestionEventListeners(questionContainer) {
        const removeQuestionBtn = questionContainer.querySelector('.remove-question-btn');
        const addWrongAnswerBtn = questionContainer.querySelector('.add-wrong-answer-btn');
        const wrongAnswersContainer = questionContainer.querySelector('.wrong-answers-container');
        
        // Обработчик удаления вопроса
        if (removeQuestionBtn) {
            removeQuestionBtn.addEventListener('click', function() {
                questionContainer.remove();
                updateQuestionNumbers();
            });
        }
        
        // Обработчик добавления неправильного ответа
        if (addWrongAnswerBtn) {
            addWrongAnswerBtn.addEventListener('click', function() {
                const questionIndex = this.getAttribute('data-question-index');
                addWrongAnswerField(questionIndex);
            });
        }
        
        // Инициализация видимости кнопок удаления неправильных ответов
        updateWrongAnswerRemoveButtons(wrongAnswersContainer);
    }
    
    // Функция для добавления поля неправильного ответа
    function addWrongAnswerField(questionIndex) {
        const container = document.getElementById(`wrong-answers-container-${questionIndex}`);
        if (!container) return;
        
        const wrapper = document.createElement('div');
        wrapper.className = 'wrong-answer-input-wrapper';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.name = `questions[${questionIndex}][wrong_answers][]`;
        input.className = 'form-control wrong-answer-input';
        input.placeholder = '{{__("Wrong answer")}}';
        
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-danger remove-wrong-answer-btn';
        removeButton.textContent = '{{__("Remove")}}';
        removeButton.addEventListener('click', function() {
            wrapper.remove();
            updateWrongAnswerRemoveButtons(container);
        });
        
        wrapper.appendChild(input);
        wrapper.appendChild(removeButton);
        container.appendChild(wrapper);
        
        updateWrongAnswerRemoveButtons(container);
    }
    
    // Функция для обновления видимости кнопок удаления неправильных ответов
    function updateWrongAnswerRemoveButtons(container) {
        const wrappers = container.querySelectorAll('.wrong-answer-input-wrapper');
        const removeButtons = container.querySelectorAll('.remove-wrong-answer-btn');
        
        // Показываем кнопки удаления только если есть более одного поля
        if (wrappers.length > 1) {
            removeButtons.forEach(btn => btn.style.display = 'inline-block');
        } else {
            removeButtons.forEach(btn => btn.style.display = 'none');
        }
    }
    
    // Настройка обработчиков событий для существующих вопросов
    function setupInitialEventListeners() {
        const questionContainers = questionsContainer.querySelectorAll('.question-container');
        questionContainers.forEach(container => {
            setupQuestionEventListeners(container);
        });
        
        // Обработчик для кнопок добавления неправильных ответов (глобальные)
        document.querySelectorAll('.add-wrong-answer-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const questionIndex = this.getAttribute('data-question-index');
                addWrongAnswerField(questionIndex);
            });
        });
        
        // Обработчик для кнопок удаления неправильных ответов (глобальные)
        document.querySelectorAll('.remove-wrong-answer-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const wrapper = this.closest('.wrong-answer-input-wrapper');
                const container = wrapper.closest('.wrong-answers-container');
                wrapper.remove();
                updateWrongAnswerRemoveButtons(container);
            });
        });
    }
    
    // Обработчик клика на кнопку добавления вопроса
    addQuestionBtn.addEventListener('click', addQuestion);
    
    // Инициализация
    setupInitialEventListeners();
    updateQuestionNumbers();
});
</script>
@endsection
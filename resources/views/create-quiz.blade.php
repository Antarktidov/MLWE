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
</style>
<form action="#" method="post">
    @csrf
    <div class="mb-3">
      <label for="title" class="form-label">{{__('Title of trivia quiz')}}</label>
      <input type="text" name="title" class="form-control" id="title">
    </div>
    <div class="mb-3">
        <label for="qusetion">{{__('Question')}} <span class="question-number">1</span></label>
        <input type="text" name="qusetion" class="form-control" id="qusetion">
    </div>
    <div class="mb-3">
        <label for="answe">{{__('Correct answer')}}</label>
        <input type="text" name="answer" class="form-control" id="answer">
    </div>
    <div class="mb-3">
        <label>{{__('Wrong answers')}}</label>
        <div class="wrong-answers-container" id="wrong-answers-container">
            <div class="wrong-answer-input-wrapper">
                <input type="text" name="wrong_answers[]" class="form-control wrong-answer-input" placeholder="{{__('Wrong answer')}}">
                <button type="button" class="btn btn-danger remove-wrong-answer-btn" style="display: none;">{{__('Remove')}}</button>
            </div>
        </div>
        <button type="button" class="btn btn-warning" id="add-wrong-answer-btn">{{__('Add wrong answer')}}</button>
    </div>
    <button class="btn btn-success">
        {{ __('Add new question') }}
    </button>
    <button class="btn btn-primary">
        {{ __('Save quiz') }}
    </button>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('wrong-answers-container');
    const addButton = document.getElementById('add-wrong-answer-btn');
    
    // Функция для добавления нового поля неверного ответа
    function addWrongAnswerField() {
        const wrapper = document.createElement('div');
        wrapper.className = 'wrong-answer-input-wrapper';
        
        const input = document.createElement('input');
        input.type = 'text';
        input.name = 'wrong_answers[]';
        input.className = 'form-control wrong-answer-input';
        input.placeholder = '{{__("Wrong answer")}}';
        
        const removeButton = document.createElement('button');
        removeButton.type = 'button';
        removeButton.className = 'btn btn-danger remove-wrong-answer-btn';
        removeButton.textContent = '{{__("Remove")}}';
        removeButton.addEventListener('click', function() {
            wrapper.remove();
            updateRemoveButtonsVisibility();
        });
        
        wrapper.appendChild(input);
        wrapper.appendChild(removeButton);
        container.appendChild(wrapper);
        
        updateRemoveButtonsVisibility();
    }
    
    // Функция для обновления видимости кнопок удаления
    function updateRemoveButtonsVisibility() {
        const wrappers = container.querySelectorAll('.wrong-answer-input-wrapper');
        const removeButtons = container.querySelectorAll('.remove-wrong-answer-btn');
        
        // Показываем кнопки удаления только если есть более одного поля
        if (wrappers.length > 1) {
            removeButtons.forEach(btn => btn.style.display = 'inline-block');
        } else {
            removeButtons.forEach(btn => btn.style.display = 'none');
        }
    }
    
    // Обработчик клика на кнопку добавления
    addButton.addEventListener('click', addWrongAnswerField);
    
    // Инициализация видимости кнопок удаления
    updateRemoveButtonsVisibility();
});
</script>
@endsection
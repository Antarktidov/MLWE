@extends('layouts.app')
@section('content')
<style>
    .wrong-answer-input-wrapper {
    display: flex;
    gap: 10px;
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
        <label for="wrong-answer-1">{{__('Wrong answer')}}</label>
        <div class="wrong-answer-input-wrapper">
            <input type="text" name="wrong-answer-1" class="form-control" id="wrong-answer-1"> <button class="btn btn-warning">{{__('Add wrong answer')}}</button>
        </div>
    </div>
    <button class="btn btn-success">
        {{ __('Add new question') }}
    </button>
    <button class="btn btn-primary">
        {{ __('Save quiz') }}
    </button>
</form>
@endsection
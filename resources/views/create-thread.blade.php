@extends('layouts.app')
@section('content')
<h1>Новое сообщение в категории форума: {{$article->title}}</h1>
<form action="{{ route('forum_messages.store', [$wiki->url, $article->url_title]) }}" method="post">
    @csrf
    <div class="mb-3">
      <label for="title" class="form-label">{{__('Title')}}</label>
      <input type="text" name="title" class="form-control" id="title">
    </div>
    <div class="mb-3">
        <label  class="form-label" for="content">{{__('Message text')}}</label>
        <textarea name="content" class="form-control" id="content"></textarea>
    </div>
    <button type="submit" class="btn btn-primary">{{__('Save')}}</button>
  </form>
@endsection
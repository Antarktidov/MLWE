@extends('layouts.app')
@section('content')
<h1>Категория форума: {{$article->title}}</h1>
<a href="{{route( 'forum.topic.create_thread', [$wiki->url, $article->url_title]) }}"  class="btn btn-primary">Новое сообщение</a>
@endsection
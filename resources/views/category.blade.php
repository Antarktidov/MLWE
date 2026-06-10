@extends('layouts.app')
@section('content')
<h1>Категория: {{ $category->name }}</h1>
<ul>
@foreach ($articles as $article)
    <li><a href="{{ route('articles.show', [$wiki->url, $article->url_title]) }}">{{$article->title}}</a></li>
@endforeach
</ul>
@endsection
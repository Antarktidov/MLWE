@extends('layouts.app')
@section('content')
<h1>Категория: {{ $category->name }}</h1>
<ul>
@foreach ($articles as $article)
    <li>{{$article->title}}</li>
@endforeach
</ul>
@endsection
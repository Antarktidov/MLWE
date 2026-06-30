@extends('layouts.app')
@section('content')
<style>
.blog {
    border: 1px solid;
    padding: 10px;
    margin-bottom: 5px;
}
</style>
<h1>Блог учатника: {{$author->name}}</h1>
@foreach ($articles as $blog)
<section>
    <div class="blog">
        <a href="{{ route('blogs.show', [$wiki->url, $blog->author_id, $blog->url_title]) }}">
            <h2>{{$blog->title}}</h2>
            <div class="content">{{$blog->last_revision_content}}</div>
        </a>
    </div>
</section>
@endforeach
@endsection
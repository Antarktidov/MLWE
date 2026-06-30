@extends('layouts.app')
@section('content')
<style>
.blog {
    border: 1px solid;
    max-height: 500px;
    overflow: clip;
    position: relative;
    margin-bottom: 5px;
}

.blog__inner {
    height: 100%;
    overflow: clip;
    padding: 10px;

    mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
    -webkit-mask-image: linear-gradient(to bottom, black 80%, transparent 100%);
}

</style>
<h1>Блог учатника: {{$author->name}}</h1>
@foreach ($articles as $blog)
<section>
    <div class="blog">
        <div class="blog__inner">
        <a href="{{ route('blogs.show', [$wiki->url, $blog->author_id, $blog->url_title]) }}">
            <h2>{{$blog->title}}</h2>
            <div class="content">@php
        $content = $blog->last_revision_content;
        $content = Str::of($content)->markdown([
            'html_input' => 'strip',
        ]);
        $content = preg_replace('/<a\b[^>]*>(.*?)<\/a>/is', '$1', $content);
        @endphp
        {!! $content !!}</div>
        </a>
        </div>
    </div>
</section>
@endforeach
{{ $articles->links() }}
@endsection
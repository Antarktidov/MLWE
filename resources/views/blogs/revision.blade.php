@extends('layouts.app')
@section('content')
    <h1>{{ $revision->title }}</h1>
    <div class="links">
        <a href="{{ route('blogs.show', [$wiki->url, $article->url_title]) }}" class="btn btn-secondary">{{ __('Back to blog') }}</a>
    </div>
    <p class="mt-3">{!! Str::of($revision->content)->markdown([
        'html_input' => 'strip',
    ]) !!}</p>
@endsection

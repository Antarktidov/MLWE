@extends('layouts.app')
@section('content')
    <h1>{{ $revision->title }}</h1>
    <div class="links">
    @if ($canEditBlog)
    <form action="{{ route('blogs.restore', [$wiki->url, $article->url_title]) }}" method="post">
        @csrf
        <button class="btn btn-success" type="submit">{{ __('Restore') }}</button>
    </form>
    @endif
    </div>
    <p class="mt-3">{!! Str::of($revision->content)->markdown([
        'html_input' => 'strip',
    ]) !!}</p>
@endsection

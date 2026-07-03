@extends('layouts.app')
@section('content')
    <h1>{{ $revision->title }}</h1>
    <div class="links">
    @if ($canEditHtmlPages)
    <form action="{{ route('html.restore', [$wiki->url, $article->url_title]) }}" method="post">
        @csrf
        <button class="btn btn-success" type="submit">{{ __('Restore') }}</button>
    </form>
    @endif
    @can('view_deleted_articles', $wiki->url)
    <!--<a href=" route('html.deleted.history', [$wiki->url, $author->id, $article->url_title]) " class="btn btn-secondary"> __('History') </a>-->
    @endcan
    </div>
    <p class="mt-3">{{$revision->content}}</p>
@endsection

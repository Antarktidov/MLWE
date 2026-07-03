@extends('layouts.app')
@section('content')
@if (count($articles) !== 0)
<h1>{{ __('All blogs') }}</h1>
    <table class="table">
        <thead>
          <tr>
            <th scope="col">id</th>
            <th scope="col">{{ __('Title') }}</th>
          </tr>
        </thead>
          <tbody>
    @foreach ($articles as $article)
    <tr>
        <th scope="row">{{ $article->id }}</th>
        <th scope="row"><a href="{{ route('html.show', [$wiki->url, $article->url_title]) }}">{{ $article->title }}</a></th>
    </tr>
    @endforeach
          </tbody>
    </table>
@endif
@if (count($articles) === 0)
<p>{{ __('There are no html pages on the wiki yet.') }}
    @auth
        <a href="{{ route('html.create', $wiki->url) }}">{{ __('Create your first html page') }}</a>.
    @endauth
</p>
@endif
@endsection

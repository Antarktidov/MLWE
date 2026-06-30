@extends('layouts.app')
@section('content')
@if (count($articles) !== 0)
<h1>{{ __('Deleted blogs') }}</h1>
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
        @php
            $author = $article->author_id ? \App\Models\User::find($article->author_id) : null;
        @endphp
        <th scope="row"><a href="{{ route('blogs.trash.show', [$wiki->url, $author->id, $article->url_title]) }}">{{ $article->title }}</a></th>
    </tr>
    @endforeach
          </tbody>
    </table>
@endif
@if (count($articles) === 0)
<p>{{ __('There are no deleted blogs on this wiki') }}</p>
@endif
@endsection

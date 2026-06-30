@extends('layouts.app')
@section('content')
@if (count($articles) !== 0)
<h1>{{ __('All blogs') }}</h1>
    <table class="table">
        <thead>
          <tr>
            <th scope="col">id</th>
            <th scope="col">{{ __('Title') }}</th>
            <th scope="col">{{ __('Author') }}</th>
          </tr>
        </thead>
          <tbody>
    @foreach ($articles as $article)
    @php
        $author = $article->author_id ? \App\Models\User::find($article->author_id) : null;
    @endphp
    <tr>
        <th scope="row">{{ $article->id }}</th>
        <th scope="row"><a href="{{ route('blogs.show', [$wiki->url, $author->id,  $article->url_title]) }}">{{ $article->title }}</a></th>
        <th scope="row">
            @if ($author)
                <a href="{{ route('userprofile.local.show', [$wiki->url,  $author->id, $author->id]) }}">{{ $author->name }}</a>
            @else
                —
            @endif
        </th>
    </tr>
    @endforeach
          </tbody>
    </table>
@endif
@if (count($articles) === 0)
<p>{{ __('There are no blogs on the wiki yet.') }}
    @auth
        <a href="{{ route('blogs.create', $wiki->url) }}">{{ __('Create your first blog') }}</a>.
    @else
        {{ __('Log in to create a blog.') }}
    @endauth
</p>
@endif
@endsection

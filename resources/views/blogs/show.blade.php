@extends('layouts.app')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/article.css') }}">
    @if ($trivia != null)
        <script>
            var quizId = {{ $trivia->id }};
        </script>
    @endif
    @if ($poll != null)
        <script>
            var pollId = {{ $poll->id }};
            var userCanVoteInPoll = {{ $permissions->can_vote_in_poll ? 'true' : 'false' }};
            var userAlreadyVotedInPull = {{ $permissions->already_voted ? 'true' : 'false' }};
        </script>
    @endif
    <script>
        var article_id = {{ $article->id }};
    </script>
    <script src="{{ asset('js/utils.js') }}" defer></script>
    <script src="{{ asset('js/cat.js') }}" defer></script>
    <h1>{{ $revision->title }}</h1>
    @if ($author)
        <p class="text-secondary">
            {{ __('Author') }}:
            <a href="{{ route('userprofile.local.show', [$wiki->url, $author->id]) }}">{{ $author->name }}</a>
        </p>
    @endif
    <div class="links">
        @if ($canEditBlog)
            <a href="{{ route('blogs.edit', [$wiki->url, $author->id, $article->url_title]) }}" class="btn btn-primary">{{ __('Edit') }}</a>
            <!--<a href=" route('blogs.history', [$wiki->url, $author->id, $article->url_title]) " class="btn btn-secondary"> __('History') </a>-->
            <form action="{{ route('blogs.destroy', [$wiki->url, $author->id, $article->url_title]) }}" method="post">
                @csrf
                @method('delete')
                <button class="btn btn-danger" type="submit">{{ __('Delete') }}</button>
            </form>
        @endif
    </div>
    <p class="mt-3">{!! Str::of($revision->content)->markdown([
        'html_input' => 'strip',
    ]) !!}</p>
    <span class="categories-list">
    <!--foreach ($categories as $cat)
        <span class="border rounded p-1 category-item m-1" data-cat-id=" $cat->id ">
            <span class="cat-item-body"><a href=" route('category.show', [$wiki->url, $cat->name]) "> $cat->name </a></span>
            <span class="cat-item-remove text-danger" onclick="removeCat( $cat->id )">x</span>
        </span>
    endforeach
    </span>
        <input name="new-category" class="border rounded m-1" type="text" placeholder="new category">
        <button class="border rounded m-1 p-1" type="submit" onclick="addCat()">Add category</button>
        -->
    @if ($is_comments_enabled)
    <div id="comments"
         data-wiki-name="{{ $wiki->url }}"
         data-article-name="{{ $article->url_title }}"
         data-page-type="blog"
         data-user-id="{{ $userId }}"
         data-user-name="{{ $userName }}"
         data-user-can-delete-comments="{{ $userCanDeleteComments ? 'true' : 'false' }}"
         data-user-can-approve-comments="{{ $userCanApproveComments ? 'true' : 'false' }}"
         >
    </div>
    @endif
@endsection
@section('right-column')
    @if (count($images) > 0)
        <div class="ri-header fw-bold">{{ __('Recent images') }}</div>
    @endif
<div class="recent-images">
    @foreach ($images as $img)
    <div class="recent-img-item-wrapper">
        <img class="recent-img-item" src="{{ asset('/storage/images/' . rawurlencode($img->filename)) }}" alt="" loading="lazy">
        <div class="time ms-auto fst-italic text-secondary">{{ $img->updated_at->diffForHumans() }}</div>
    </div>
    @endforeach
</div>
    @if ($trivia != null)
        <div class="trivia">
            <h4>{{ $trivia->title }}</h4>
            <div class="trivia-body m-3">
                <center><button onclick="showQuestion(0)" class="btn btn-outline-warning">{{ __('Start') }}</button></center>
            </div>
        </div>
        <script src="{{ asset('js/article-trivia.js') }}" defer></script>
    @endif
    @if ($poll != null)
        <div class="poll">
            <h4>{{ $poll->title }}</h4>
            <div class="trivia-body m-3">
                @foreach ($poll['variants'] as $i => $var)
                    <div {{ $permissions->can_vote_in_poll ? 'onclick=votePoll(' . ($i + 1) . ')' : '' }} data-poll-answer_idx="{{ $i + 1 }}" class="variant answer">{{ $var }}</div>
                @endforeach
            </div>
        </div>
        <script src="{{ asset('js/poll.js') }}" defer></script>
    @endif
@endsection

@extends('layouts.app')
@section('content')
    <link rel="stylesheet" href="{{ asset('css/article.css') }}">
    @if ( $trivia != null )
        <script>
                var quizId = {{$trivia->id}};
        </script>
    @endif
    @if ( $poll != null )
        <script>
                var pollId = {{$poll->id}};
                var userCanVoteInPoll = {{$userCanVoteInPoll ? 'true': 'false'}};
                var userAlreadyVotedInPull = {{ $userAlreadyVotedInPull ? 'true': 'false' }};
        </script>
    @endif
    <script src="{{ asset('js/utils.js') }}" defer></script>
    <h1>{{$revision->title}}</h1>
    <div class="links">
    <a href="{{route('articles.edit', [$wiki->url, $article->url_title])}}" class="btn btn-primary">{{__('Edit')}}</a>
    <a href="{{route('articles.history', [$wiki->url, $article->url_title])}}" class="btn btn-secondary">{{__('History')}}</a>
    @can('delete', $wiki->url)
    <form action="{{route('articles.destroy',  [$wiki->url, $article->url_title])}}" method="post">
        @csrf
        @method('delete')
        <button class="btn btn-danger" type="submit">{{__('Delete')}}</button>
    </form>
    @endcan
    </div>
    @if (!$revision->is_patrolled)
    <div class="alert alert-warning mt-3" role="alert">
        {{ __("This revision hasn't been patrolled yet and can contain disinformation") }}
    </div>
    @endif
    <p class="mt-3">{!!Str::of($revision->content)->markdown([
        'html_input' => 'strip',
    ])!!}</p>
    @if ($is_comments_enabled)
    <div id="comments"
         data-wiki-name="{{ $wiki->url }}"
         data-article-name="{{ $article->url_title }}"
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
    @if ( $trivia != null )
        <div class="trivia">
            <h4>{{$trivia->title}}</h4>
            <div class="trivia-body m-3">
                <center><button onclick="showQuestion(0)" class="btn btn-outline-warning">{{__('Start')}}</button></center>
            </div>
        </div>
        <script src="{{ asset('js/article-trivia.js') }}" defer>
        </script>
    @endif
    @if ( $poll != null )
        <div class="poll">
            <h4>{{$poll->title}}</h4>
            <div class="trivia-body m-3">
                @foreach ($poll['variants'] as $i => $var )
                    <div {{$userCanVoteInPoll ? 'onclick=votePoll(' . ($i + 1) . ')': ''}} data-poll-answer_idx="{{ $i + 1 }}" class="variant answer">{{ $var }}</div>
                @endforeach
            </div>
        </div>
        <script src="{{ asset('js/poll.js') }}" defer>
        </script>
    @endif
@endsection
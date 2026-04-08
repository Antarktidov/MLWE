@extends('layouts.app')
@section('content')
    <style>
        main {
            display: grid;
            grid-template-columns: 20vw 1fr 20vw;
            margin-right: auto;
            margin-left: auto;
        }

        main .right-column {
            overflow-x: auto;
            width: 18vw;
        }

        .recent-images {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            gap: 10px;
            overflow-x: auto;
        }

        .recent-img-item {
            object-fit: cover;
                
            height: 150px;
            width: calc(18vw * 0.9);
            background-size: cover;
            background-position: center;
            flex: 0 0 calc(18vw * 0.9);
        }
        .recent-img-item-wrapper {
            & .time {
                margin-top: 0.5rem;
                margin-bottom: 0.5rem;
            }
        }
        .ri-header {
            margin-bottom: 0.5rem;
        }
    </style>
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
@endsection
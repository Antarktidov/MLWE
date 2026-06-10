@extends('layouts.app')
@section('content')
<h1>{{__('Transfer artcile')}}</h1>
<form class="" action="{{route('articles.transfer.post', $article->id)}}" method="post">
  @csrf
    <div><b>Article: </b></div>
    <div><a href="{{ route('articles.show', [$original_wiki->url, $article->url_title]) }}">{{ $article->title }}</a></div>
    <div><b>Current wiki: </b></div>
    <div>{{ $original_wiki->url }}</div>
    <label><b>Wiki to transfer:</b></label>
    <select class="form-control mt-1" name="wiki-to-transfer" id="wiki-to-transfer">
        @foreach ($all_wikis as $wiki)
          @if($wiki->id !== $original_wiki->id)
            <option value="{{ $wiki->id }}">{{ $wiki->url  }}</option>
          @endif
        @endforeach
    </select>
    <button type="submit" class="mt-4 btn btn-primary">{{__('Transfer article')}}</button>
</form>
@endsection
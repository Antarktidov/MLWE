@extends('layouts.app')
@section('content')
<form action="{{route('forum.topic.store', $wiki->url)}}" method="post">
    @csrf
    <div class="mb-3">
      <label for="url" class="form-label">{{__('Forum topic name')}}</label>
      <input type="text" name="title" class="form-control" id="title">
    </div>
    <button type="submit" class="btn btn-primary">{{__('Create topic')}}</button>
</form>
@endsection
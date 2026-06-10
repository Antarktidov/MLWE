@extends('layouts.app')
@section('content')
<h1>{{__('Transfer artcile')}}</h1>
<form class="" action="{{route('articles.transfer.post')}}" method="post">
  @csrf
    <div><b>Current wiki: </b></div>
    <div>Название текущей вики</div>
    <label><b>Wiki to transfer:</b></label>
    <select class="form-control mt-1" name="wiki-to-transfer" id="wiki-to transfer">
        <option value="wiki-1">Wiki 1</option>
        <option value="wiki-2">Wiki 2</option>
    </select>
    <button type="submit" class="mt-4 btn btn-primary">{{__('Transfer article')}}</button>
</form>
@endsection
@extends('layouts.app')
@section('content')
<h1>Блог учатника: {{$author->id}}</h1>
@foreach ($articles as $blog)
<section>
    <h2>{{$blog->title}}</h2>
    <!--<div class="content">$blog</div>-->
</section>
@endforeach
@endsection
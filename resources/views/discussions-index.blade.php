@extends('layouts.app')
@section('content')
<div id="discussions-index-root"
    data-wiki-name="{{ $wiki->url }}"
    data-user-id="{{ $userId }}"
    data-user-name="{{ $userName }}"
    data-wiki-id="{{ $wikiId }}"
    data-user-can-moderate_discussions="{{ $userCanModerateDiscussions ? 'true' : 'false' }}"
    >
</div>
@endsection
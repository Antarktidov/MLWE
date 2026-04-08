@extends('layouts.app')
@section('content')
<h1>{{__('Manage wikifarm')}}</h1>
<form action="{{ route('manage_wikifarm.update') }}" method="post">
  @csrf
  <strong>{{__('Wikifarm asses level')}}</strong>
  <div class="form-check">
      <input class="form-check-input" type="radio" name="protection_level" id="public" value="public"
       {{ $options->protection_level === 'public' ? 'checked' : '' }}>
      <label class="form-check-label" for="public">
        {{ __("Open wiki1") }}
      </label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="protection_level" id="comments_only" value="comments_only"
      {{ $options->protection_level === 'comments_only' ? 'checked' : '' }}>
      <label class="form-check-label" for="comments_only">
        {{ __("Semi-closed1") }}
      </label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="protection_level" id="semi_public" value="semi_public"
      {{ $options->protection_level === 'semi_public' ? 'checked' : '' }}>
      <label class="form-check-label" for="semi_public">
        {{ __("Semi-closed2") }}
      </label>
    </div>
    <div class="form-check">
      <input class="form-check-input" type="radio" name="protection_level" id="private" value="private"
      {{ $options->protection_level === 'private' ? 'checked' : '' }}>
      <label class="form-check-label" for="private">
        {{ __("Closed wiki") }}
      </label>
    </div>
  <br>
  <div class="form-check">
    <input type="checkbox" class="form-check-input" id="is_comments_enabled" name="is_comments_enabled"
    {{ $options->is_comments_enabled? 'checked' : '' }}>
    <label class="form-check-label" for="is_comments_enabled">
    {{ __('Is comments enabled') }}</label>
  </div>
  <div class="mb-3 form-check">
    <input type="checkbox" class="form-check-input" id="is_registration_enabled" name="is_registration_enabled"
    {{ $options->is_registration_enabled ? 'checked' : '' }}>
    <label class="form-check-label" for="is_registration_enabled">
    {{ __('Is registration enabled') }}</label>
  </div>
  <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
</form>
@endsection
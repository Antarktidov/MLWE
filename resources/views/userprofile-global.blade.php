@extends('layouts.app')
@section('content')
<script>
  var userId = {{ $user->id }};
  @if($user_profile)
  var upRevId = {{ $user_profile->id }};
  @else
  var upRevId = null;
  @endif
</script>
<script src="{{ asset('js/user-profile-util.js') }}" defer></script>
<link rel="stylesheet" href="{{asset('css/profile.css')}}">
<style>
  .profile-banner {
    background: @if($user_profile && $user_profile->banner) {{ $user_profile->banner }} @else linear-gradient(135deg, var(--bs-secondary) 0%, var(--bs-dark) 100%)@endif;
  }
  /* Placeholder для аватара/баннера — загрузка будет на бэкенде */
  .profile-avatar {
    background: @if($user_profile && $user_profile->avatar) {{ $user_profile->avatar }} @else var(--bs-secondary) @endif;
  }
</style>
<div class="border rounded overflow-hidden">
  <div class="profile-banner">
  </div>

  <div class="p-3 p-md-4">
    <div class="d-flex flex-wrap align-items-start gap-3 mb-4">
      {{-- Аватар (заглушка: управление на бэкенде) --}}
      <div class="profile-avatar flex-shrink-0">
        {{-- TODO: вывод avatar когда будет реализована загрузка/хранение на бэкенде --}}
        @if($user_profile && $user_profile->avatar)
          {{-- <img src="..." alt="" class="rounded-circle w-100 h-100 object-fit-cover"> --}}
        @else
        <span class="opacity-50">?</span>
        @endif
      </div>

      <div class="flex-grow-1 min-w-0">
        <div class="profile-header mb-2">
          <h1 class="mb-0">{{ $user->name }}</h1>
          @if($user_profile && $user_profile->aka)
            <span class="text-muted">aka</span>
            <h3 class="mb-0 fs-5 text-muted">{{ $user_profile->aka }}</h3>
          @endif
          @if($can_review_user_profiles && $user_profile && !$user_profile->is_approved)
            <button class="btn btn-success" onclick="approveRev()">{{__('Approve')}}</button>
          @endif
          @if($can_review_user_profiles || $is_my_profile)
            <button class="btn btn-danger"  onclick="deleteProfile()">{{__('Delete')}}</button>
          @endif
          @if($is_my_profile)
            <a href="{{ route('userprofile.global.edit', $user) }}" class="btn btn-primary">{{__('Edit')}}</a>
          @endif
          @if($friend_status === 'not_friends')
            <button class="btn btn-primary" onclick="addFriend()">{{__('Add to friends')}}</button>
          @endif
          @if($friend_status === 'your_friend_request_is_pending')
            <button onclick="cancelFriendRequest()" class="btn btn-danger">{{__('Cancel friend request')}}</button>
          @endif
          @if($friend_status === 'this_user_wants_add_you_to_friends')
            <button onclick="acceptFriendRequest()" class="btn btn-success">{{__('Accept friend request')}}</a>
            <button class="btn btn-danger">{{__('Decline friend request')}}</a>
          @endif
        </div>
        @if($user_group_names)
          <div class="d-flex flex-wrap gap-1">
            @foreach($user_group_names as $name)
              <span class="user-group-name theme-aware bg-gradient px-2 py-1 rounded">{{ $name }}</span>
            @endforeach
          </div>
        @endif
        @if($user_profile && $user_profile->i_live_in)
          <p class="text-muted mt-2 mb-0 small">{{ __('I live in: ') }} {{ $user_profile->i_live_in }}</p>
        @endif
      </div>
    </div>

    @if($user_profile)
      @if($user_profile->about)
        <section class="mb-4">
          <h5 class="border-bottom pb-1 mb-2">{{__('About')}}</h5>
          <div class="text-break">{{ nl2br(e($user_profile->about)) }}</div>
        </section>
      @endif

      @php
        $links = array_filter([
          'Discord' => $user_profile->discord ?? null,
          __('Discord bot') => $user_profile->discord_if_bot ?? null,
          __('VK') => $user_profile->vk ?? null,
          __('Telegram') => $user_profile->telegram ?? null,
          'GitHub' => $user_profile->github ?? null,
        ]);
      @endphp
      @if(!empty($links))
        <section>
          <h5 class="border-bottom pb-1 mb-2">{{__('Links')}}</h5>
          <ul class="list-unstyled d-flex flex-wrap gap-3 mb-0">
            @foreach($links as $label => $url)
              <li>
                @if(filter_var($url, FILTER_VALIDATE_URL))
                  <a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="profile-social-link">
                    {{ $label }}
                  </a>
                @else
                  <span class="profile-social-link">{{ $label }}: {{ e($url) }}</span>
                @endif
              </li>
            @endforeach
          </ul>
        </section>
      @endif
    @else
      <p class="text-muted mb-0">{{ __("The profile has not been filled out yet.") }}</p>
    @endif
    @if(!empty($medals) || $can_manage_global_medals)
        <section>
          <h5 class="border-bottom pb-1 mb-2">{{__("Rewards")}}</h5>
            @if(!empty($medals))
            <div class="all-medals">
            @foreach($medals as $medal)
            <div class="medal-wrapper">
              <div style="border: 1px solid; width: 300px;" class="nagrada mb-2">
                  <div class="nagrada-name"><strong>{{ $medal->name }}</strong></div>
                  <div class="nagrada-body">  
                    <div class="">
                        <img
                        width="100"
                        height="100"
                        src="{{asset('storage/' . $medal->image) }}">
                      </div>
                      <div class="">
                        {{ $medal->description }}
                      </div>
                    </div>
                    <div class="nagrada-giver">Награда от <a href="{{ route('userprofile.global.show', $medal->giver_id) }}">{{$medal->giver_name}}</a></div>
              </div>
              @can('manage_global_medals', $wiki->url)
              <form action="{{ route('medals.take-away', [$user, $medal->id]) }}" method="post">
                @csrf
                @method('delete')
                <button class="btn btn-danger" type="submit">{{__("Take away medal")}}</button>
              </form>
              @endcan
              </div>
            @endforeach
            </div>
            @endif
            @can('manage_global_medals', $wiki->url)
              <h6 class="border-bottom pb-1 mb-2">{{__("Give medal")}}</h6>
              <form action="{{ route('medals.give', $user) }}" method="post">
                @csrf
                <select name="medal" id="medal" class="form-select mb-2" aria-label="Default select example">
                  <option selected>{{ __("Choose a medal") }}</option>
                    @foreach ($all_medals as $medal )
                    <option value="{{$medal->id}}">{{$medal->name}}</option>
                  @endforeach
                </select>
                <button class="btn btn-primary" type="submit">{{__("Give")}}</button>
              </form>
            @endcan
        </section>
      @endif
  </div>
</div>
@endsection
@section('right-column')
<div class="user-friends">
  <h2>{{__('Friends')}}</h2>
  <div id="user-friends-svelte" data-user-id={{ $user->id }}></div>
</div>
@endsection
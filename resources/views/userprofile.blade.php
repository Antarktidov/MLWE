@extends('layouts.app')
@section('content')
@php
  $banner = optional($user_profile_local)->banner ?? optional($user_profile)->banner;
  $avatar = optional($user_profile_local)->avatar ?? optional($user_profile)->avatar;
  $aka = optional($user_profile_local)->aka ?? optional($user_profile)->aka;
  $i_live_in = optional($user_profile_local)->i_live_in ?? optional($user_profile)->i_live_in;
  $about = optional($user_profile_local)->about ?? optional($user_profile)->about;
  $discord = optional($user_profile_local)->discord ?? optional($user_profile)->discord;
  $discord_if_bot = optional($user_profile_local)->discord_if_bot ?? optional($user_profile)->discord_if_bot;
  $vk = optional($user_profile_local)->vk ?? optional($user_profile)->vk;
  $telegram = optional($user_profile_local)->telegram ?? optional($user_profile)->telegram;
  $github = optional($user_profile_local)->github ?? optional($user_profile)->github;
  $profile_id = optional($user_profile_local)->id ?? optional($user_profile)->id;
  $is_approved_effective = optional($user_profile_local)->is_approved ?? optional($user_profile)->is_approved;
  $has_profile = $user_profile_local ?? $user_profile;
@endphp
<script>
  var userId = {{ $user->id }};
  var upRevId = @json($profile_id);
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
        @if($avatar)
          {{-- <img src="..." alt="" class="rounded-circle w-100 h-100 object-fit-cover"> --}}
        @else
        <span class="opacity-50">?</span>
        @endif
      </div>

      <div class="flex-grow-1 min-w-0">
        <div class="profile-header mb-2">
          <h1 class="mb-0">{{ $user->name }}</h1>
          @if($aka)
            <span class="text-muted">aka</span>
            <h3 class="mb-0 fs-5 text-muted">{{ $aka }}</h3>
          @endif
          @if($can_review_user_profiles && $has_profile && !$is_approved_effective)
            <button class="btn btn-success" onclick="approveRev()">{{__('Approve')}}</button>
          @endif
          @if($can_review_user_profiles || $is_my_profile)
            <button class="btn btn-danger"  onclick="deleteProfile()">{{__('Delete')}}</button>
          @endif
          @if($is_my_profile)
            <a href="{{ route('userprofile.local.edit', [$wiki->url, $user]) }}" class="btn btn-primary">{{__('Edit')}}</a>
          @endif
        </div>
        @if($user_group_names)
          <div class="d-flex flex-wrap gap-1">
            @foreach($user_group_names as $name)
              <span class="user-group-name theme-aware bg-gradient px-2 py-1 rounded">{{ $name }}</span>
            @endforeach
          </div>
        @endif
        @if($i_live_in)
          <p class="text-muted mt-2 mb-0 small">{{ __('I live in: ') }} {{ $i_live_in }}</p>
        @endif
      </div>
    </div>

    @if($has_profile)
      @if($about)
        <section class="mb-4">
          <h5 class="border-bottom pb-1 mb-2">О себе</h5>
          <div class="text-break">{!! nl2br(e($about)) !!}</div>
        </section>
      @endif

      @php
        $links = array_filter([
          'Discord' => $discord,
          __('Discord bot') => $discord_if_bot,
          __('VK') => $vk,
          __('Telegram') => $telegram,
          'GitHub' => $github,
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

    @if(!empty($medals)
        || ($can_manage_global_medals && $all_medals_global->isNotEmpty())
        || ($can_manage_medals && $all_medals_local->isNotEmpty()))
        <section class="mt-4">
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
                        alt=""
                        src="{{ asset('storage/' . $medal->image) }}">
                      </div>
                      <div class="">
                        {{ $medal->description }}
                      </div>
                    </div>
                    @if((int) $medal->award_wiki_id === 0)
                      <div class="nagrada-giver small text-muted">{{ __("Global reward") }}</div>
                    @else
                      <div class="nagrada-giver small text-muted">{{ __("Local reward") }} ({{ $wiki->url }})</div>
                    @endif
                    <div class="nagrada-giver">{{ __("Reward by") }}
                      @if($medal->giver_id)
                        <a href="{{ route('userprofile.global.show', $medal->giver_id) }}">{{ $medal->giver_name }}</a>
                      @else
                        {{ $medal->giver_name }}
                      @endif
                    </div>
              </div>
              @php
                $can_take = auth()->user() && (
                  ((int) $medal->award_wiki_id === 0 && auth()->user()->can('manage_global_medals', $wiki->url))
                  || ((int) $medal->award_wiki_id !== 0 && auth()->user()->can('manage_medals', $wiki->url))
                );
              @endphp
              @if($can_take)
              <form action="{{ route('medals.local.take-away', [$wiki->url, $user, $medal->id]) }}" method="post">
                @csrf
                @method('delete')
                <input type="hidden" name="award_wiki_id" value="{{ (int) $medal->award_wiki_id }}">
                <button class="btn btn-danger" type="submit">{{__("Take away medal")}}</button>
              </form>
              @endif
              </div>
            @endforeach
            </div>
            @endif
            @if(($can_manage_global_medals && $all_medals_global->isNotEmpty()) || ($can_manage_medals && $all_medals_local->isNotEmpty()))
              <h6 class="border-bottom pb-1 mb-2 mt-3">{{__("Give medal")}}</h6>
              <form action="{{ route('medals.local.give', [$wiki->url, $user]) }}" method="post">
                @csrf
                <select name="medal" id="medal-local-profile" class="form-select mb-2" required aria-label="Медаль">
                  <option value="" disabled selected>{{ __("Choose a medal") }}</option>
                  @if($can_manage_global_medals && $all_medals_global->isNotEmpty())
                    <optgroup label="{{ __("Global medals") }}">
                      @foreach ($all_medals_global as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                      @endforeach
                    </optgroup>
                  @endif
                  @if($can_manage_medals && $all_medals_local->isNotEmpty())
                    <optgroup label="{{ __("Local medals") }}">
                      @foreach ($all_medals_local as $m)
                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                      @endforeach
                    </optgroup>
                  @endif
                </select>
                <button class="btn btn-primary" type="submit">{{__("Give")}}</button>
              </form>
            @endif
        </section>
      @endif
  </div>
</div>
@endsection
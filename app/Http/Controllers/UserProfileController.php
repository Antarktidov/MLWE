<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\UserProfileRevision;
use App\Models\User;
use App\Models\UserUserGroupWiki;
use App\Models\UserGroup;
use App\Models\Wiki;
use App\Models\Medal;
use App\Models\UserMedal;

use App\Helpers\FriendsHelper;

class UserProfileController extends Controller
{
    public function show_global(User $user) {

        $friend_status = FriendsHelper::check_friend_status_with_this_user($user);

        $user_group_ids = UserUserGroupWiki::where('user_id', $user->id)
            ->where('wiki_id', 0)
            ->pluck('user_group_id')
            ->unique();

        $user_group_names = UserGroup::whereIn('id', $user_group_ids)
            ->pluck('name')
            ->values()
            ->all();

        $wiki = Wiki::withTrashed()->first();
        $user2 = auth()->user();
        if ($user2 != null) {
            if ($wiki) {
                $can_review_user_profiles = $user2->can('review_user_profiles', $wiki->url);
                $can_manage_global_medals = $user2->can('manage_global_medals', $wiki->url);
            } else {
                $can_review_user_profiles = false;
                $can_manage_global_medals = false;
            }
            $is_my_profile = $user2->id === $user->id;
        } else {
            $can_review_user_profiles = false;
            $is_my_profile = false;
        }

        if ($can_review_user_profiles || $is_my_profile) {
            $user_profile = $this->latestProfileRevision($user->id, 0);
        } else {
            $user_profile = $this->latestProfileRevision($user->id, 0, true);
        }

        $user_medals = UserMedal::where('user_id', $user->id)
        ->where('wiki_id', 0)
        ->get();

        $medals = [];
        $medal_by_id = Medal::whereIn('id', $user_medals->pluck('medal_id')->unique())
            ->get()
            ->keyBy('id');
        $giver_by_id = User::whereIn('id', $user_medals->pluck('giver_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        foreach ($user_medals as $um) {
            $medal = $medal_by_id->get($um->medal_id);
            if ($medal) {
                $medal_with_meta = clone $medal;
                $giver = $giver_by_id->get($um->giver_id);
                $medal_with_meta->giver_name = $giver ? $giver->name : '?';
                $medal_with_meta->giver_id = $giver ? $giver->id : null;
                $medals[] = $medal_with_meta;
            }
        }

        $all_medals = Medal::where('wiki_id', 0)->orderBy('name')->get();

        return view('userprofile-global', compact('user_profile', 'user',
                                        'user_group_names', 'can_review_user_profiles',
                                        'is_my_profile', 'medals', 'can_manage_global_medals',
                                        'wiki', 'all_medals', 'friend_status'));
    }

    public function show_local(string $wikiName, User $user) {

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $user_group_ids = UserUserGroupWiki::where('user_id', $user->id)
            ->whereIn('wiki_id', [0, $wiki->id])
            ->pluck('user_group_id')
            ->unique();

        $user_group_names = UserGroup::whereIn('id', $user_group_ids)
            ->pluck('name')
            ->values()
            ->all();

        $user2 = auth()->user();
        if ($user2 != null) {
            $can_review_user_profiles = $user2->can('review_user_profiles', $wiki->url);
            $is_my_profile = $user2->id === $user->id;
        } else {
            $can_review_user_profiles = false;
            $is_my_profile = false;
        }

        if ($can_review_user_profiles || $is_my_profile) {
            $user_profile = $this->latestProfileRevision($user->id, 0);
            $user_profile_local = $this->latestProfileRevision($user->id, $wiki->id);
        } else {
            $user_profile = $this->latestProfileRevision($user->id, 0, true);
            $user_profile_local = $this->latestProfileRevision($user->id, $wiki->id, true);
        }

        if ($user2 != null) {
            $can_manage_global_medals = $user2->can('manage_global_medals', $wiki->url);
            $can_manage_medals = $user2->can('manage_medals', $wiki->url);
        } else {
            $can_manage_global_medals = false;
            $can_manage_medals = false;
        }

        $user_medals = UserMedal::where('user_id', $user->id)
            ->where(function ($query) use ($wiki) {
                $query->where('wiki_id', 0)
                    ->orWhere('wiki_id', $wiki->id);
            })
            ->orderByRaw('CASE WHEN wiki_id = 0 THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->get();

        $medals = [];
        $medal_by_id = Medal::whereIn('id', $user_medals->pluck('medal_id')->unique())
            ->get()
            ->keyBy('id');
        $giver_by_id = User::whereIn('id', $user_medals->pluck('giver_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        foreach ($user_medals as $um) {
            $medal = $medal_by_id->get($um->medal_id);
            if ($medal) {
                $medal_with_meta = clone $medal;
                $giver = $giver_by_id->get($um->giver_id);
                if ($giver) {
                    $medal_with_meta->giver_name = $giver->name;
                    $medal_with_meta->giver_id = $giver->id;
                } else {
                    $medal_with_meta->giver_name = '?';
                    $medal_with_meta->giver_id = null;
                }
                $medal_with_meta->award_wiki_id = $um->wiki_id;
                $medals[] = $medal_with_meta;
            }
        }

        $all_medals_global = Medal::where('wiki_id', 0)->orderBy('name')->get();
        $all_medals_local = Medal::where('wiki_id', $wiki->id)->orderBy('name')->get();
        //dd($all_medals_local);

        return view('userprofile', compact('user_profile', 'user_profile_local', 'user',
                                        'user_group_names', 'can_review_user_profiles',
                                        'is_my_profile', 'wiki', 'medals', 'can_manage_global_medals',
                                        'can_manage_medals', 'all_medals_global', 'all_medals_local'));
    }

    public function approve(UserProfileRevision $up_rev) {
        $up_rev->update([
            'is_approved' => true,
        ]);
        return response(__('The user profile revision has been approved'), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function delete(User $user) {
        $wiki = Wiki::withTrashed()->first();
        $user2 = auth()->user();
        if (!($user2 != null && $user2->id === $user->id) ||
            !($user2->can('review_user_profiles', $wiki->url))) {
            abort(403);
        }


        $up_revs = UserProfileRevision::where('user_id', $user->id)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')->get();

        foreach ($up_revs as $rev) {
            $rev->delete();
        }
        return response(__('The user profile has been deleted'), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function edit_global(User $user) {

        $user2 = auth()->user();
        if (!($user2 != null && $user2->id === $user->id)) {
            abort(403);
        }

        
        $user_profile = UserProfileRevision::where('user_id', $user->id)
        ->where('wiki_id', 0)
        ->whereNull('deleted_at')
        ->orderBy('id', 'desc')->first();

        return view('userprofile-global-edit', compact('user_profile', 'user'));
    }

    public function edit_local(string $wikiName, User $user) {

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }
        $user2 = auth()->user();
        if (!($user2 != null && $user2->id === $user->id)) {
            abort(403);
        }

        
        $user_profile = UserProfileRevision::where('user_id', $user->id)
        ->where('wiki_id', $wiki->id)
        ->whereNull('deleted_at')
        ->orderBy('id', 'desc')->first();

        return view('userprofile-edit', compact('user_profile', 'user', 'wiki'));
    }

    public function store_global(User $user) {
        $data = request()->validate([
            'avatar'          => ['nullable', 'string'],
            'banner'          => ['nullable', 'string'],
            'about'           => ['nullable', 'string'],
            'aka'             => ['nullable', 'string'],
            'i_live_in'       => ['nullable', 'string'],
            'discord'         => ['nullable', 'string'],
            'discord_if_bot'  => ['nullable', 'string'],
            'vk'              => ['nullable', 'string'],
            'telegram'        => ['nullable', 'string'],
            'github'          => ['nullable', 'string'],
        ]);

        $user2 = auth()->user();
        if (!($user2 != null && $user2->id === $user->id)) {
            abort(403);
        }

        $up_rev = [
            'avatar'          => $data['avatar'] ?? null,
            'banner'          => $data['banner'] ?? null,
            'about'           => $data['about'] ?? null,
            'aka'             => $data['aka'] ?? null,
            'i_live_in'       => $data['i_live_in'] ?? null,
            'discord'         => $data['discord'] ?? null,
            'discord_if_bot'  => $data['discord_if_bot'] ?? null,
            'vk'              => $data['vk'] ?? null,
            'telegram'        => $data['telegram'] ?? null,
            'github'          => $data['github'] ?? null,

            //значения, устанаваливаемые сервером
            'is_approved'     => false,
            'wiki_id'         => 0,
            'user_id'         => $user->id,
        ];

        UserProfileRevision::create($up_rev);

        return response(__('The user profile has been successfully updated'), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function store_local(string $wikiName, User $user) {

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $data = request()->validate([
            'avatar'          => ['nullable', 'string'],
            'banner'          => ['nullable', 'string'],
            'about'           => ['nullable', 'string'],
            'aka'             => ['nullable', 'string'],
            'i_live_in'       => ['nullable', 'string'],
            'discord'         => ['nullable', 'string'],
            'discord_if_bot'  => ['nullable', 'string'],
            'vk'              => ['nullable', 'string'],
            'telegram'        => ['nullable', 'string'],
            'github'          => ['nullable', 'string'],
        ]);

        $user2 = auth()->user();
        if (!($user2 != null && $user2->id === $user->id)) {
            abort(403);
        }

        $up_rev = [
            'avatar'          => $data['avatar'] ?? null,
            'banner'          => $data['banner'] ?? null,
            'about'           => $data['about'] ?? null,
            'aka'             => $data['aka'] ?? null,
            'i_live_in'       => $data['i_live_in'] ?? null,
            'discord'         => $data['discord'] ?? null,
            'discord_if_bot'  => $data['discord_if_bot'] ?? null,
            'vk'              => $data['vk'] ?? null,
            'telegram'        => $data['telegram'] ?? null,
            'github'          => $data['github'] ?? null,

            //значения, устанаваливаемые сервером
            'is_approved'     => false,
            'wiki_id'         => $wiki->id,
            'user_id'         => $user->id,
        ];

        UserProfileRevision::create($up_rev);

        return response(__('The user profile has been successfully updated'), 200)
            ->header('Content-Type', 'text/plain');
    }

    private function latestProfileRevision(int $userId, int $wikiId, bool $onlyApproved = false): ?UserProfileRevision
    {
        $query = UserProfileRevision::where('user_id', $userId)
            ->where('wiki_id', $wikiId)
            ->whereNull('deleted_at');

        if ($onlyApproved) {
            $query->where('is_approved', true);
        }

        return $query->latest('id')->first();
    }
}

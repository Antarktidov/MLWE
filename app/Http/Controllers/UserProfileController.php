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

class UserProfileController extends Controller
{
    public function show_global(User $user) {

        $user_usergroups_wiki = UserUserGroupWiki::where('user_id', $user->id)
        ->where('wiki_id', 0)
        ->select('user_group_id')
        ->get();

        $user_group_names = [];

        foreach($user_usergroups_wiki as $item) {
            $user_group_names[] = UserGroup::find($item->user_group_id)->name;
        }

        $wiki = Wiki::withTrashed()->first();
        $user2 = auth()->user();
        if ($user2 != null) {
            if ($wiki) {
                $can_review_user_profiles = $user2->can('review_user_profiles', $wiki->url);
            } else {
                $can_review_user_profiles = false;
            }
            $is_my_profile = $user2->id === $user->id;
        } else {
            $can_review_user_profiles = false;
            $is_my_profile = false;
        }

        if ($can_review_user_profiles || $is_my_profile) {
            $user_profile = UserProfileRevision::where('user_id', $user->id)
            ->where('wiki_id', 0)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')->first();
        } else {
            $user_profile= UserProfileRevision::where('user_id', $user->id)
            ->where('wiki_id', 0)
            ->where('is_approved', true)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')->first();
        }

        $user_medals = UserMedal::where('user_id', $user->id)
        ->where('wiki_id', 0)
        ->get();

        $medals = [];
        //dd($user_medals);

        foreach ($user_medals as $um) {
            $medals[] = Medal::where('user_id', $um->id)
            ->first();
        }

        return view('userprofile-global', compact('user_profile', 'user',
                                        'user_group_names', 'can_review_user_profiles',
                                        'is_my_profile', 'user_medals'));
    }

    public function show_local(string $wikiName, User $user) {

        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain');
        }

        $user_usergroups_wiki = UserUserGroupWiki::where('user_id', $user->id)
        ->where(function ($query) use ($wiki) {
            $query->where('wiki_id', 0)
                  ->orWhere('wiki_id', $wiki->id);
        })
        ->select('user_group_id')
        ->get();

        $user_group_names = [];

        foreach($user_usergroups_wiki as $item) {
            $user_group_names[] = UserGroup::find($item->user_group_id)->name;
        }

        $user2 = auth()->user();
        if ($user2 != null) {
            $can_review_user_profiles = $user2->can('review_user_profiles', $wiki->url);
            $is_my_profile = $user2->id === $user->id;
        } else {
            $can_review_user_profiles = false;
            $is_my_profile = false;
        }

        if ($can_review_user_profiles || $is_my_profile) {
            $user_profile = UserProfileRevision::where('user_id', $user->id)
            ->where('wiki_id', 0)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')->first();

            $user_profile_local = UserProfileRevision::where('user_id', $user->id)
            ->where('wiki_id', $wiki->id)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')->first();
        } else {
            $user_profile = UserProfileRevision::where('user_id', $user->id)
            ->where('wiki_id', 0)
            ->where('is_approved', true)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')->first();

            $user_profile_local = UserProfileRevision::where('user_id', $user->id)
            ->where('wiki_id', $wiki->id)
            ->where('is_approved', true)
            ->whereNull('deleted_at')
            ->orderBy('id', 'desc')->first();
        }

        return view('userprofile', compact('user_profile', 'user_profile_local', 'user',
                                        'user_group_names', 'can_review_user_profiles',
                                        'is_my_profile', 'wiki'));
    }

    public function approve(UserProfileRevision $up_rev) {
        $up_rev->update([
            'is_approved' => true,
        ]);
        return response(__('The user profile revision has been approved'), 200)
            ->header('Content-Type', 'text/plain');
    }

    public function delete(User $user) {
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
}

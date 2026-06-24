<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserMedal;
use App\Models\Medal;

class MedalService
{
    public function getUserMedalsWithMeta(int $userId, int $wikiId): array
    {
        $userMedals = UserMedal::where('user_id', $userId)
            ->where(fn($q) => $q->where('wiki_id', 0)->orWhere('wiki_id', $wikiId))
            ->orderByRaw('CASE WHEN wiki_id = 0 THEN 0 ELSE 1 END')
            ->orderBy('id')
            ->get();

        $medals = Medal::whereIn('id', $userMedals->pluck('medal_id')->unique())
            ->get()
            ->keyBy('id');

        $givers = User::whereIn('id', $userMedals->pluck('giver_id')->filter()->unique())
            ->get()
            ->keyBy('id');

        return $userMedals->map(function ($um) use ($medals, $givers) {
            $medal = clone $medals[$um->medal_id];
            $giver = $givers[$um->giver_id] ?? null;

            $medal->giver_name = $giver->name ?? '?';
            $medal->giver_id = $giver->id ?? null;
            $medal->award_wiki_id = $um->wiki_id;

            return $medal;
        })->all();
    }

    public function getAllMedals(int $wikiId): array
    {
        return [
            'global' => Medal::where('wiki_id', 0)->orderBy('name')->get(),
            'local'  => Medal::where('wiki_id', $wikiId)->orderBy('name')->get(),
        ];
    }
}

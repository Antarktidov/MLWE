<?php

namespace App\Services;

use App\Models\UserUserGroupWiki;
use App\Models\UserGroup;

class GroupService
{
    public function getUserGroups(int $userId, int $wikiId): array
    {
        $groupIds = UserUserGroupWiki::where('user_id', $userId)
            ->whereIn('wiki_id', [0, $wikiId])
            ->pluck('user_group_id')
            ->unique();

        return UserGroup::whereIn('id', $groupIds)
            ->pluck('name')
            ->values()
            ->all();
    }
}
<?php

namespace App\Services;

class ProfileService
{
    public function getProfiles(int $userId, int $wikiId, array $permissions): array
    {
        $hidden = !$permissions['can_review'] && !$permissions['is_my_profile'];

        return [
            'global' => $this->latestProfileRevision($userId, 0, $hidden),
            'local'  => $this->latestProfileRevision($userId, $wikiId, $hidden),
        ];
    }

    private function latestProfileRevision(int $userId, int $wikiId, bool $hidden = false)
    {
        return app('App\Http\Controllers\UserProfileController')
            ->latestProfileRevision($userId, $wikiId, $hidden);
    }
}
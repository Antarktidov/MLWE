<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserGroup;
use App\Models\UserUserGroupWiki;
use App\Models\Wiki;
class UserRightsController extends Controller
{
    //TODO: разделить логику управления локальными и глоабьными группами, избавиться от дублирующегося кода

    //Форма для управления глобальными группами
    public function manage_global_user_rights(int $userId) {
        $managed_user = User::findOrFail($userId);
        $user_groups = UserGroup::where('is_global', 1)->get();
        $user_user_group_wiki = UserUserGroupWiki::where('wiki_id', 0)
            ->where('user_id', $userId)
            ->get();

        return view('manage_user_groups', compact('managed_user', 'user_groups', 'user_user_group_wiki'));
    }
    //POST-ручка для сохранения глобальных прав участника
    //Уровень доступа - глобальная группа steward
    public function store_global_user_rights(int $userId) {
        $data = request()->validate([
            'user_group_ids' => 'array',
        ]);

        User::findOrFail($userId);//ищем управляемого участника
        $currentUserGroupIds = UserUserGroupWiki::where('user_id', $userId)
            ->where('wiki_id', 0)
            ->pluck('user_group_id')
            ->all();

        $user_group_ids = array_map('intval', $data['user_group_ids'] ?? []);
        $user_group_ids_to_remove = array_values(array_diff($currentUserGroupIds, $user_group_ids));
        $user_group_ids_to_add = array_values(array_diff($user_group_ids, $currentUserGroupIds));

        if (count($user_group_ids_to_remove) > 0) {
            UserUserGroupWiki::where('user_id', $userId)
                ->where('wiki_id', 0)
                ->whereIn('user_group_id', $user_group_ids_to_remove)
                ->delete();
        }

        if (count($user_group_ids_to_add) > 0) {
            $rowsToInsert = array_map(static function ($groupId) use ($userId) {
                return [
                    'user_id' => $userId,
                    'user_group_id' => $groupId,
                    'wiki_id' => 0,
                ];
            }, $user_group_ids_to_add);

            UserUserGroupWiki::insert($rowsToInsert);
        }

        return response(__('The user\'s usergroups was changed'), 200)
            ->header('Content-Type', 'text/plain');
    }


    /**
     * Управление локальными групами. Уровень дсотупа - steward и admin
     */
    public function manage_local_user_rights(string $wikiName, int $userId) {
        $wiki = Wiki::where('url', $wikiName)->first();
        if ($wiki) {
            $managed_user = User::findOrFail($userId);
            $user_groups = UserGroup::where('is_global', 0)->get();
            $user_user_group_wiki = UserUserGroupWiki::where('wiki_id', $wiki->id)
                ->where('user_id', $userId)
                ->get();

            return view('manage_local_user_groups', compact('wiki', 'managed_user', 'user_groups', 'user_user_group_wiki'));
        }
    }
    /**
     * POST-ручка для управления локальными группами
     */
    public function store_local_user_rights(string $wikiName, int $userId) {
        $wiki = Wiki::where('url', $wikiName)->first();
        if ($wiki) {
            $data = request()->validate([
                'user_group_ids' => 'array',
            ]);
            User::findOrFail($userId);

            $currentUserGroupIds = UserUserGroupWiki::where('user_id', $userId)
                ->where('wiki_id', $wiki->id)
                ->pluck('user_group_id')
                ->all();

            $user_group_ids = array_map('intval', $data['user_group_ids'] ?? []);
            $user_group_ids_to_remove = array_values(array_diff($currentUserGroupIds, $user_group_ids));
            $user_group_ids_to_add = array_values(array_diff($user_group_ids, $currentUserGroupIds));

            if (count($user_group_ids_to_remove) > 0) {
                UserUserGroupWiki::where('user_id', $userId)
                    ->where('wiki_id', $wiki->id)
                    ->whereIn('user_group_id', $user_group_ids_to_remove)
                    ->delete();
            }

            if (count($user_group_ids_to_add) > 0) {
                $rowsToInsert = array_map(static function ($groupId) use ($userId, $wiki) {
                    return [
                        'user_id' => $userId,
                        'user_group_id' => $groupId,
                        'wiki_id' => $wiki->id,
                    ];
                }, $user_group_ids_to_add);

                UserUserGroupWiki::insert($rowsToInsert);
            }

            return response(__('The user\'s usergroups was changed'), 200)
                ->header('Content-Type', 'text/plain');
        }
    }
}

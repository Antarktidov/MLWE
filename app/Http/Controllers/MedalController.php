<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserMedal;
use App\Models\Medal;
use App\Models\Wiki;

class MedalController extends Controller
{
    public function give(Request $request, User $user) {
        $data = request()->validate([
            'medal' => 'required|integer',
        ]);
        
        $medal_id = $data['medal'];
        $giver = auth()->user();

        $db_medal = Medal::find($medal_id);
        if (!$db_medal || (int) $db_medal->wiki_id !== 0) {
            return response('Это не глобальная медаль', 422)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $user_medal = [
            'medal_id' => $medal_id,
            'giver_id' => $giver->id,
            'user_id' => $user->id,
            'wiki_id' => 0,
        ];

        $user_medal2 = UserMedal::where('medal_id', $user_medal['medal_id'])
        ->where('giver_id', $user_medal['giver_id'])
        ->where('user_id', $user_medal['user_id'])
        ->where('wiki_id', $user_medal['wiki_id'])
        ->first();

        if ($user_medal2 != null) {
            return response('У участника уже есть такая медаль', 422)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        UserMedal::create($user_medal);
        return response('Медаль выдана', 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function take_away(Request $request, User $user, Medal $medal) {
        $medal_id = $medal->id;
        $giver = auth()->user();

        $user_medal2 = UserMedal::where('medal_id', $medal_id)
        ->where('user_id', $user->id)
        ->where('wiki_id', 0)
        ->first();

        if (!$user_medal2) {
            return response('Запись о медали не найдена', 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $user_medal2->delete();

        return response('Медаль отобрана', 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * Выдача глобальной (wiki_id 0) или локальной медали текущей вики с локальной страницы профиля.
     */
    public function give_local(Request $request, string $wikiName, User $user) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $data = $request->validate([
            'medal' => ['required', 'integer'],
        ]);

        $giver = auth()->user();
        $db_medal = Medal::find($data['medal']);
        if (!$db_medal) {
            return response('Медаль не найдена', 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $mWiki = (int) $db_medal->wiki_id;
        if ($mWiki === 0) {
            if (!$giver->can('manage_global_medals', $wiki->url)) {
                return response('Forbidden', 403)
                    ->header('Content-Type', 'text/plain; charset=UTF-8');
            }
            $targetWikiId = 0;
        } elseif ($mWiki === (int) $wiki->id) {
            if (!$giver->can('manage_medals', $wiki->url)) {
                return response('Forbidden', 403)
                    ->header('Content-Type', 'text/plain; charset=UTF-8');
            }
            $targetWikiId = $wiki->id;
        } else {
            return response('Эта медаль не относится к данной вики', 422)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $user_medal = [
            'medal_id' => $db_medal->id,
            'giver_id' => $giver->id,
            'user_id' => $user->id,
            'wiki_id' => $targetWikiId,
        ];

        $existing = UserMedal::where('medal_id', $user_medal['medal_id'])
            ->where('giver_id', $user_medal['giver_id'])
            ->where('user_id', $user_medal['user_id'])
            ->where('wiki_id', $user_medal['wiki_id'])
            ->first();

        if ($existing !== null) {
            return response('У участника уже есть такая медаль', 422)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        UserMedal::create($user_medal);

        return response('Медаль выдана', 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * Снятие медали с локальной страницы профиля (глобальной или локальной для этой вики).
     */
    public function take_away_local(Request $request, string $wikiName, User $user, Medal $medal) {
        $wiki = Wiki::where('url', $wikiName)->whereNull('deleted_at')->first();
        if (!$wiki) {
            return response(__('Wiki does not exist'), 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $awardWikiId = (int) $request->input('award_wiki_id', -1);
        if (!in_array($awardWikiId, [0, (int) $wiki->id], true)) {
            return response('Forbidden', 403)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $giver = auth()->user();
        if ($awardWikiId === 0) {
            if (!$giver->can('manage_global_medals', $wiki->url)) {
                return response('Forbidden', 403)
                    ->header('Content-Type', 'text/plain; charset=UTF-8');
            }
        } else {
            if (!$giver->can('manage_medals', $wiki->url)) {
                return response('Forbidden', 403)
                    ->header('Content-Type', 'text/plain; charset=UTF-8');
            }
        }

        $user_medal2 = UserMedal::where('medal_id', $medal->id)

            ->where('user_id', $user->id)
            ->where('wiki_id', $awardWikiId)
            ->first();

        if (!$user_medal2) {
            return response('Запись о медали не найдена', 404)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
        }

        $user_medal2->delete();

        return response('Медаль отобрана', 200)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}

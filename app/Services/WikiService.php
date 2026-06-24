<?php

namespace App\Services;

use App\Models\Wiki;

class WikiService
{
    public function getActiveWiki(string $url): ?Wiki
    {
        return Wiki::where('url', $url)->whereNull('deleted_at')->first();
    }

    public function notFoundResponse()
    {
        return response(__('Wiki does not exist'), 404)
            ->header('Content-Type', 'text/plain');
    }
}

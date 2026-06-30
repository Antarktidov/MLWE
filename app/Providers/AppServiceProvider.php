<?php

namespace App\Providers;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\Paginator;

/*use App\Models\User;
use App\Models\Wiki;
use App\Models\UserGroup;
use App\Models\UserUserGroupWiki;*/
use App\Helpers\PermissionChecker;
use Illuminate\Support\Facades\Schema;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    
    /**
     * Bootstrap any application services.
     */

public function boot(): void
{
    Schema::defaultStringLength(191);
    
    Gate::define('edit_articles', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_edit_articles');
    });

    Gate::define('create_articles', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_create_articles');
    });

    Gate::define('delete', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_delete_articles');
    });
    
    Gate::define('restore', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_restore_articles');
    });

    Gate::define('view_deleted_articles', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_view_deleted_articles');
    });

    Gate::define('view_revision_user_ip', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_view_revision_user_ip');
    });
    

    Gate::define('delete_revisions', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_delete_revisions');
    });

    Gate::define('restore_revisions', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_restore_revisions');
    });

    Gate::define('create_wikis', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_create_wikis');
    });

    Gate::define('close_wikis', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_close_wikis');
    });

    Gate::define('open_wikis', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_open_wikis');
    });
    Gate::define('delete_commons_images', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_delete_commons_images');
    });

    Gate::define('delete_comments', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_delete_comments');
    });
    
    Gate::define('check_revisions', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_check_revisions');
    });

    Gate::define('patrol_revisions', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_patrol_revisions');
    });

    Gate::define('check_comments', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_check_comments');
    });

    Gate::define('check_images', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_check_images');
    });

    Gate::define('manage_permissions', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_manage_permissions');
    });

    Gate::define('review_user_profiles', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_review_user_profiles');
    });

    Gate::define('manage_medals', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_manage_medals');
    });

    Gate::define('manage_global_medals', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_manage_global_medals');
    });

    Gate::define('be_filament_admin', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_be_filament_admin');
    });

    Gate::define('manage_trivia', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_manage_trivia');
    });

    Gate::define('manage_polls', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_manage_polls');
    });

    Gate::define('transfer_articles', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_transfer_articles');
    });

    Gate::define('edit_other_users_blogs', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_edit_other_users_blogs');
    });
    
    /*Gate::define('revert_edits_to_old_version', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_revert_edits_to_old_version');
    });
    
    Gate::define('oldedit', function ($user, $wikiName) {
        return PermissionChecker::check($user, $wikiName, 'can_oldedit');
    });*/
    //Пагинация
    Paginator::useBootstrapFive();
}

}
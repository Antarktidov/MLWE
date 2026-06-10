<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WikisController;
use App\Http\Controllers\ArticleController;
use App\Http\Controllers\RevisionController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\UserRightsController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\PermissionsManagerController;
use App\Http\Controllers\OptionsController;
use App\Http\Controllers\UserProfileController;
use App\Http\Controllers\MedalController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\PollsController;
use App\Http\Controllers\FriendsController;
use App\Http\Controllers\CategoryController;

use App\Http\Middleware\DeleteMiddleware;
use App\Http\Middleware\DeleteRevisionMiddleware;
use App\Http\Middleware\RestoreRevisionMiddleware;
use App\Http\Middleware\ViewDeletedMiddleware;
use App\Http\Middleware\ViewDeletedRevisionsMiddleware;
use App\Http\Middleware\RestoreMiddleware;

use App\Http\Middleware\CreateWikisMiddleware;
use App\Http\Middleware\CloseWikisMiddleware;
use App\Http\Middleware\OpenWikisMiddleware;
use App\Http\Middleware\ManageGlobalUserrightsMiddleware;
use App\Http\Middleware\ManageLocalUserrightsMiddleware;

use App\Http\Middleware\DeleteImagesMiddleware;

use App\Http\Middleware\DeleteCommentsMiddleware;

use App\Http\Middleware\ManageTriviaMiddleware;
use App\Http\Middleware\ManagePollsMiddleware;

//Approve and patrol feature middlewares
use App\Http\Middleware\ApproveRevisionMiddleware;
use App\Http\Middleware\PatrolRevisionMiddleware;
use App\Http\Middleware\ApproveCommentMiddleware;
use App\Http\Middleware\ApproveImageMiddleware;
//End of approve and patrol feature middlewares

use App\Http\Middleware\PermissionManagerMiddleware;
use App\Http\Middleware\ManageWikifarmMiddleware;

use App\Http\Middleware\CommentsEnabledMiddleware;
use App\Http\Middleware\ProtectionLevel1Middleware;
use App\Http\Middleware\ProtectionLevel2Middleware;
use App\Http\Middleware\ProtectionLevel3Middleware;

use App\Http\Middleware\ReviewUserProfilesMiddleware;

use App\Models\Option;//волшебный код, который может положить всё приложение

use App\Http\Middleware\ManageMedalsMiddleware;
use App\Http\Middleware\ManageGlobalMedalsMiddleware;
use App\Http\Middleware\TransferArticlesMiddleware;

Route::middleware([ProtectionLevel3Middleware::class])->group(function () {

    //Работа с САМИМИ викиями
    Route::get('/', [WikisController::class,'index'])->name('index');
    Route::get('/create-wiki', [WikisController::class,'create'])->name('wikis.create')
    ->middleware(CreateWikisMiddleware::class);
    Route::post('/store', [WikisController::class,'store'])->name('wikis.store')
    ->middleware(CreateWikisMiddleware::class);
    Route::delete('/destroy/{wiki}', [WikisController::class,'destroy'])->name('wikis.destroy')
    ->middleware(CloseWikisMiddleware::class);
    Route::post('/open/{wiki}', [WikisController::class,'restore'])->name('wikis.open')
    ->middleware(OpenWikisMiddleware::class);
    Route::get('/closed-wikis', [WikisController::class,'trash'])->name('wikis.show_closed')
    ->middleware(OpenWikisMiddleware::class);

    //Работа с правами на вики
    //1. Глобальными
    Route::get('/global-user-rights/{userId}', [UserRightsController::class,'manage_global_user_rights'])->name('wikis.global_userrights')
    ->middleware(ManageGlobalUserrightsMiddleware::class);
    Route::post('/global-user-rights/{userId}/store', [UserRightsController::class,'store_global_user_rights'])->name('wikis.global_userrights.store')
    ->middleware(ManageGlobalUserrightsMiddleware::class);
    //2. Локальными
    Route::get('/wiki/{wikiName}/user-rights/{userId}', [UserRightsController::class,'manage_local_user_rights'])->name('wikis.local_userrights')
        ->middleware(ManageLocalUserrightsMiddleware::class);
    Route::post('/wiki/{wikiName}/user-rights/{userId}/store', [UserRightsController::class,'store_local_user_rights'])->name('wikis.local_userrights.store')
        ->middleware(ManageLocalUserrightsMiddleware::class);

    //Работа с общими изображениями
    Route::get('/commons/upload', [ImageController::class,'create'])->name('images.upload_page')
    ->middleware([ProtectionLevel1Middleware::class, ProtectionLevel2Middleware::class]);
    Route::post('/commons/store', [ImageController::class,'store'])->name('images.store')
    ->middleware([ProtectionLevel1Middleware::class, ProtectionLevel2Middleware::class]);
    Route::get('/commons', [ImageController::class,'index'])->name('images.gallery');
    Route::delete('/commons/delete/{image}', [ImageController::class,'destroy'])->name('images.delete')
    ->middleware(DeleteImagesMiddleware::class);
    Route::get('/private-images/{filename}', [ImageController::class, 'show_private'])->name('private.image')
    ->middleware(ApproveImageMiddleware::class);
    Route::get('/approvers_commons', [ImageController::class,'approvers_index'])->name('images.approvers_gallery')
    ->middleware(ApproveImageMiddleware::class);
    Route::post('/commons/approve/{image}', [ImageController::class,'approve'])->name('images.approve')
    ->middleware(ApproveImageMiddleware::class);

    //Работа со статьями на викиях
    Route::get('/wiki/{wikiName}/all-articles', [ArticleController::class,'index'])->name('index.articles');
    Route::get('/wiki/{wikiName}/article/{articleName}', [ArticleController::class,'show'])->name('articles.show');
    Route::get('/wiki/{wikiName}/article/{articleName}/edit', [ArticleController::class,'edit'])->name('articles.edit')
    ->middleware([ProtectionLevel1Middleware::class, ProtectionLevel2Middleware::class]);
    Route::get('/wiki/{wikiName}/create-article', [ArticleController::class,'create'])->name('articles.create')
    ->middleware([ProtectionLevel1Middleware::class, ProtectionLevel2Middleware::class]);
    Route::post('/wiki/{wikiName}/store', [ArticleController::class,'store'])->name('articles.store')
    ->middleware(ProtectionLevel1Middleware::class);
    Route::post('/wiki/{wikiName}/update/{articleName}/edit', [ArticleController::class,'update'])->name('articles.update')
    ->middleware([ProtectionLevel1Middleware::class, ProtectionLevel2Middleware::class]);
    Route::delete('/wiki/{wikiName}/{articleName}/destroy', [ArticleController::class,'destroy'])->name('articles.destroy')
    ->middleware(DeleteMiddleware::class);
    Route::get('/wiki/{wikiName}/trash', [ArticleController::class,'trash'])->name('articles.trash')
    ->middleware(ViewDeletedMiddleware::class);
    Route::get('/wiki/{wikiName}/trash/article/{articleName}', [ArticleController::class,'show_deleted'])
    ->name('articles.trash.show')
    ->middleware(ViewDeletedMiddleware::class);
    Route::post('/wiki/{wikiName}/{articleName}/restore', [ArticleController::class,'restore'])->name('articles.restore')
        ->middleware(RestoreMiddleware::class);

    //трансфер статей
    Route::post('/transfer-articles/article_id/{article}', [ArticleController::class,'transfer_articles'])
    ->name('articles.transfer.form')
    ->middleware(TransferArticlesMiddleware::class);
    Route::post('/transfer-articles-post/article_id/{article}', [ArticleController::class,'transfer_articles_post'])
    ->name('articles.transfer.post')
    ->middleware(TransferArticlesMiddleware::class);

    //Категории
    Route::post('/api/remove_category_from_article/{category}/{article}', [CategoryController::class,'remove_category_from_article'])
    ->name('category.remove');
    Route::post('/api/add_category/{article}', [CategoryController::class,'add_category'])
    ->name('category.add');

    //Работа с историей правок
    Route::get('/wiki/{wikiName}/article/{articleName}/history', [RevisionController::class,'index'])->name('articles.history');
    Route::get('/wiki/{wikiName}/trash/article/{articleName}/history', [RevisionController::class,'show_deleted_hist'])
    ->name('articles.deleted.history')
    ->middleware(ViewDeletedMiddleware::class);
    Route::get('/wiki/{wikiName}/article/{articleName}/deleted_history', [RevisionController::class,'trash'])
    ->name('articles.trash.edits')
    ->middleware(ViewDeletedRevisionsMiddleware::class);
    Route::delete('/wiki/{wikiName}/{articleName}/{revisionId}/destroy', [RevisionController::class,'destroy'])->name('revision.delete')
    ->middleware(DeleteRevisionMiddleware::class);
    Route::post('/wiki/{wikiName}/{articleName}/{revisionId}/restore', [RevisionController::class,'restore'])->name('revision.restore')
    ->middleware(RestoreRevisionMiddleware::class);
    Route::get('/wiki/{wikiName}/article/{articleName}/revision/{revisionId}', [RevisionController::class,'view'])->name('revision.show');
    Route::post('/wiki/{wikiName}/{articleName}/{revisionId}/approve', [RevisionController::class,'approve'])->name('revision.approve')
    ->middleware(ApproveRevisionMiddleware::class);
    Route::post('/wiki/{wikiName}/{articleName}/{revisionId}/patrol', [RevisionController::class,'patrol'])->name('revision.patrol')
    ->middleware(PatrolRevisionMiddleware::class);
    Route::post('/wiki/{wikiName}/{articleName}/{revisionId}/depatrol', [RevisionController::class,'depatrol'])->name('revision.depatrol')
    ->middleware(PatrolRevisionMiddleware::class);

    //Работа с комментариями под статьями
    Route::middleware([CommentsEnabledMiddleware::class])->group(function () {
        Route::get('/api/wiki/{wikiName}/article/{articleName}/comments', [CommentsController::class,'show_comments_under_article'])
        ->name('comments.show_all');
        Route::post('/api/wiki/{wikiName}/article/{articleName}/comments/store', [CommentsController::class,'store'])
        ->name('comments.store')
        ->middleware(ProtectionLevel2Middleware::class);
        Route::delete('/api/wiki/{wikiName}/article/{articleName}/comments/{comment}/delete', [CommentsController::class,'delete'])
        ->middleware(DeleteCommentsMiddleware::class)
        ->name('comments.delete');
        Route::post('/api/wiki/{wikiName}/article/{articleName}/comments/{comment}/approve', [CommentsController::class,'approve'])
        ->middleware(ApproveCommentMiddleware::class)
        ->name('comments.approve');
        Route::post('/api/wiki/{wikiName}/article/{articleName}/comments/{comment}/update', [CommentsController::class,'update'])
        ->name('comments.update');
    });

    //Работа с разрешениями групп участников
    Route::get('/permissions_manager', [PermissionsManagerController::class,'index'])->name('permissions_manager.index')
    ->middleware(PermissionManagerMiddleware::class);
    Route::post('/permissions_manager/store', [PermissionsManagerController::class,'store'])->name('permissions_manager.store')
    ->middleware(PermissionManagerMiddleware::class);
    Route::delete('/permissions_manager/delete/{perm}', [PermissionsManagerController::class,'delete_perm'])->name('permissions_manager.destroy')
    ->middleware(PermissionManagerMiddleware::class);
    Route::get('/permissions_manager/create', [PermissionsManagerController::class,'create'])->name('permissions_manager.create')
    ->middleware(PermissionManagerMiddleware::class);
    Route::post('/permissions_manager/create/store', [PermissionsManagerController::class,'store_usergroup'])->name('permissions_manager.store_usergroup')
    ->middleware(PermissionManagerMiddleware::class);

    //Manage Wikifarm
    Route::get('/manage_wikifarm', [OptionsController::class,'index'])->name('manage_wikifarm.index')
    ->middleware(ManageWikifarmMiddleware::class);
    Route::post('/manage_wikifarm/update', [OptionsController::class,'update'])->name('manage_wikifarm.update')
    ->middleware(ManageWikifarmMiddleware::class);

    //Глобальные профили
    Route::get('/userprofile-global/{user}', [UserProfileController::class, 'show_global'])->name('userprofile.global.show');
    Route::post('/userprofile-global/{up_rev}/approve', [UserProfileController::class, 'approve'])->name('userprofile.global.approve')
    ->middleware(ReviewUserProfilesMiddleware::class);
    Route::delete('/userprofile-global/{user}/delete', [UserProfileController::class, 'delete'])->name('userprofile.global.delete');
    Route::get('/userprofile-global/{user}/edit', [UserProfileController::class, 'edit_global'])->name('userprofile.global.edit');
    Route::post('/userprofile-global/{user}/store', [UserProfileController::class, 'store_global'])->name('userprofile.global.store');

    //Локальные профили
    Route::get('/wiki/{wikiName}/userprofile/{user}', [UserProfileController::class, 'show_local'])->name('userprofile.local.show');
    Route::get('/wiki/{wikiName}/userprofile/{user}/edit', [UserProfileController::class, 'edit_local'])->name('userprofile.local.edit');
    Route::post('/wiki/{wikiName}/userprofile/{user}/store', [UserProfileController::class, 'store_local'])->name('userprofile.local.store');

    //Medals
    Route::post('/give-medal/{user}/', [MedalController::class, 'give'])->name('medals.give')
    ->middleware(ManageGlobalMedalsMiddleware::class);
    Route::delete('/take-medal-away/{user}/{medal}', [MedalController::class, 'take_away'])->name('medals.take-away')
    ->middleware(ManageGlobalMedalsMiddleware::class);

    Route::post('/wiki/{wikiName}/give-medal/{user}', [MedalController::class, 'give_local'])->name('medals.local.give')
        ->middleware(ManageGlobalMedalsMiddleware::class);
    Route::delete('/wiki/{wikiName}/take-medal-away/{user}/{medal}', [MedalController::class, 'take_away_local'])->name('medals.local.take-away')
        ->middleware(ManageGlobalMedalsMiddleware::class);
    });

    //квизы
    Route::get('/quizes/create', [QuizController::class, 'create'])->name('quiz.create')
    ->middleware(ManageTriviaMiddleware::class);
    Route::post('/quizes/store', [QuizController::class, 'store'])->name('quiz.store')
    ->middleware(ManageTriviaMiddleware::class);
    Route::get('/api/quizes/show/{quiz}', [QuizController::class, 'show'])->name('quiz.api.show');

    //опросы (polls)
    Route::get('/polls/create', [PollsController::class, 'create'])->name('poll.create')
    ->middleware(ManagePollsMiddleware::class);
    Route::post('/polls/store', [PollsController::class, 'store'])->name('poll.store')
    ->middleware(ManagePollsMiddleware::class);
    Route::post('/api/polls/accept_vote/{poll}', [PollsController::class, 'accept_vote'])->name('poll.accept_vote')
    ->middleware('auth');
    Route::get('/api/polls/get_votes/{poll}', [PollsController::class, 'get_votes'])->name('poll.get_votes');

    //Friends (Друзья)
    Route::get('/api/user/friends/{user}', [FriendsController::class, 'get_user_friends'])->name('user.friends.get_friends');
    Route::post('/api/friends/add_friend/{friend}', [FriendsController::class, 'add_friend'])->name('user.friends.add_friend')
    ->middleware('auth');
    Route::delete('/api/friends/cancel_friends_request/{friend}', [FriendsController::class, 'cancel_friends_request'])->name('user.friends.cancel_friends_request')
    ->middleware('auth');
    Route::post('/api/friends/accept_friend_request/{friend}', [FriendsController::class, 'accept_friend_request'])->name('user.accept_friend_request')
    ->middleware('auth');
    Route::post('/api/friends/decline_friend_request/{friend}', [FriendsController::class, 'decline_friend_request'])->name('user.decline_friend_request')
    ->middleware('auth');
    Route::delete('/api/friends/delete/{friend}', [FriendsController::class, 'delete'])->name('user.friends.delete')
    ->middleware('auth');

//Логин, регистрация
$options = Option::getOptions();//волшебный код, который может положить всё приложение
Auth::routes([
    'verify' => true,
    'register' => $options->is_registration_enabled//волшебный код, который может положить всё приложение
    ]);

// Email verification routes
Route::middleware('auth')->group(function () {
    Route::get('/email/verify', [App\Http\Controllers\Auth\VerificationController::class, 'show'])->name('verification.notice');
    Route::post('/email/resend', [App\Http\Controllers\Auth\VerificationController::class, 'resend'])->name('verification.resend');
});

Route::middleware(['auth', 'signed'])->group(function () {
    Route::get('/email/verify/{id}/{hash}', [App\Http\Controllers\Auth\VerificationController::class, 'verify'])->name('verification.verify');
});

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

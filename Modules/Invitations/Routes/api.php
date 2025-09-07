<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Invitations\Http\Controllers\LeagueController;
use Modules\Invitations\Http\Controllers\InvitationMatchController;
use Modules\Invitations\Http\Controllers\MatchResultController;
use Modules\Invitations\Http\Controllers\TeamOwnerinvController;
use Modules\Invitations\Http\Controllers\TeamUsesrinvController;
use Modules\Invitations\Http\Controllers\UserMatchController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:api')->group(function () {
    Route::get('invitation-matches/open', [InvitationMatchController::class, 'openPublic']);
    Route::get('leagues/{leagueId}/teams', [InvitationMatchController::class, 'teamsByLeague']);
Route::get('invitations/accepted/league/{leagueId}', [TeamOwnerinvController::class, 'acceptedInvitationsByLeague']);
Route::get('teams/league/{leagueId}', [TeamOwnerinvController::class, 'teamsInLeague']);

Route::get('leagues/my', [LeagueController::class, 'myLeagues']);
Route::get('leagues/stadium/{stadium_id}', [LeagueController::class, 'leaguesByStadium']);

Route::post('invitations/{id}/approve', [TeamUsesrinvController::class, 'approveInvitation']);
Route::post('invitations/{id}/reject', [TeamUsesrinvController::class, 'rejectInvitation']);



      Route::get('/invitation-matches/league/{leagueId}', [InvitationMatchController::class, 'getByLeague']);
    // ✅ Routes خاصة بالـ Leagues
   // Route::apiResource('leagues', LeagueController::class);
    //Route::put('leagues-update/{id}', [LeagueController::class, 'update']);

    // ✅ Routes خاصة بالـ team_ownerinv
    Route::apiResource('team-ownerinv', TeamOwnerinvController::class)->middleware('team-ownerinv');

    // 1. كل الدعوات التي أرسلها owner لفرق
    Route::get('/invitations/owner/sent/{ownerId}', [TeamOwnerinvController::class, 'invitationsSentByOwner']);

    // 2. كل الدعوات التي استلمها owner
    Route::get('/invitations/owner/received/{ownerId}', [TeamOwnerinvController::class, 'invitationsReceivedByOwner']);

    // 3. كل الدعوات التي أرسلها فريق لمالكين
    Route::get('/invitations/team/sent-ownerinv/{teamId}', [TeamOwnerinvController::class, 'invitationsSentByTeam']);

    // 4. كل الطلبات التي استلمها فريق من مالكين
    Route::get('/invitations/team/received-ownerinv/{teamId}', [TeamOwnerinvController::class, 'invitationsReceivedByTeam']);

    // ✅ Routes خاصة بالـ team_userinv
    Route::apiResource('team-usesrinv', TeamUsesrinvController::class);

    // دعوات استلمها مستخدم (يعني الفرق بعتتله)
    Route::get('/invitations/user/received/{userId}', [TeamUsesrinvController::class, 'invitationsReceivedByUser']);

    // طلبات أرسلها مستخدم (يعني المستخدم طلب ينضم لفرق)
    Route::get('/invitations/user/sent/{userId}', [TeamUsesrinvController::class, 'invitationsSentByUser']);

    // دعوات أرسلها فريق (يعني الفريق بعت لمستخدمين)
    Route::get('/invitations/team/sent/{teamId}', [TeamUsesrinvController::class, 'invitationsSentByTeam']);

    // طلبات استلمها فريق (يعني المستخدمين طلبوا ينضموا)
    Route::get('/invitations/team/received/{teamId}', [TeamUsesrinvController::class, 'invitationsReceivedByTeam']);

    // ✅ Routes خاصة بالمباريات
    Route::apiResource('user-matches', UserMatchController::class);
    Route::apiResource('invitation-matches', InvitationMatchController::class);
    Route::apiResource('match-results', MatchResultController::class);

// باقي العمليات العامة
Route::get('leagues', [LeagueController::class, 'index']);
Route::get('leagues/{id}', [LeagueController::class, 'show']);

// Invitation Matches Actions
Route::prefix('invitation-matches')->group(function () {
    Route::post('{id}/approve', [InvitationMatchController::class, 'approve']);
    Route::post('{id}/reject', [InvitationMatchController::class, 'reject']);
    Route::post('{id}/mark-as-played', [InvitationMatchController::class, 'markAsPlayed']);
});
});


Route::prefix('leagues')->middleware(['auth:api', 'role:stadium_owner'])->group(function () {
    Route::post('/', [LeagueController::class, 'store']);     // إنشاء ليج
  // Route::put('leagues-update/{id}', [LeagueController::class, 'update']);
Route::put('/{id}', [LeagueController::class, 'update']);
    Route::delete('/{id}', [LeagueController::class, 'destroy']); // حذف ليج

    Route::post('/whith-match', [InvitationMatchController::class, 'storeWithLeagueCheck']);
Route::patch('owner-invitations/{id}/approve', [TeamOwnerinvController::class, 'approveInvitation']);
Route::patch('owner-invitations/{id}/reject', [TeamOwnerinvController::class, 'rejectInvitation']);


});



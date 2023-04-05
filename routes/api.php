<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\InvitationController;
use App\Http\Controllers\ActivityController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return new App\Http\Resources\UserResource($request->user());
    });
    Route::patch('/user/avatar', [UserController::class, 'avatar'])->name('user.avatar');

    //family
    Route::patch('families/{family}/users/{user}', [FamilyController::class, 'transferManager'])->name('families.users.manager');
    Route::delete('families/{family}/users/{user}', [FamilyController::class, 'removeAdult'])->name('families.users.delete');
    Route::apiResource('families', FamilyController::class)->only(['index', 'show', 'store']);


    //children
    Route::patch('children/{child}/avatar', [ChildController::class, 'avatar'])->name('children.avatar');
    Route::apiResource('families.children', ChildController::class)
        ->only(['store', 'show', 'update', 'destroy'])
        ->shallow();

    //invitations
    Route::post('/families/{family}/invitations', [InvitationController::class, 'store'])->name('families.invitations.store');
    Route::apiResource('invitations', InvitationController::class)
        ->only(['index', 'update']);

    //childrens activities
    Route::apiResource('children.activities', ActivityController::class)->only(['store']);
});

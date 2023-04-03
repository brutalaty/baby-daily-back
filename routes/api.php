<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\UserController;
use App\Http\Controllers\ChildController;
use App\Http\Controllers\FamilyController;
use App\Http\Controllers\InvitationController;

use App\Models\Family;
use App\Models\Child;
use App\Models\Invitation;

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
    Route::apiResource('families', FamilyController::class)->only(['index', 'show', 'store']);


    //children
    Route::patch('children/{child}/avatar', [ChildController::class, 'avatar'])->name('children.avatar');
    Route::apiResource('families.children', ChildController::class)
        ->only(['store', 'show', 'update'])
        ->shallow();

    //invitations
    Route::post('/families/{family}/invitations', [InvitationController::class, 'store'])->name('families.invitations.store');
    Route::apiResource('invitations', InvitationController::class)
        ->only(['index', 'update']);
});






Route::middleware(['auth:sanctum'])->get('/poops', function (Request $request) {
    $format = "Y-m-d-H-i";
    return response()->json([
        (object) [
            'id' => 1,
            'time' => date($format, strtotime("-1 hour")),
        ],
        (object) [
            'id' => 2,
            'time' => date($format, strtotime("-20 minutes")),
        ],
        (object) [
            'id' => 3,
            'time' => date($format, strtotime("+1 hour")),
        ]
    ]);
})->name('poops.get');

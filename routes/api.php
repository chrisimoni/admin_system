<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(['middleware'=>'api'], function() {
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/create-user', [UserController::class, 'createUser']);
    Route::patch('/update-user/{id}', [UserController::class, 'updateUser']);
    Route::delete('/delete-user/{id}', [UserController::class, 'deleteUser']);
    Route::get('/get-pending-requests', [UserController::class, 'getPendingRequest']);
    Route::post('/approve-or-reject-request', [UserController::class, 'approveOrRejectRequest']);
    Route::post('/logout', [UserController::class, 'logout']);
});




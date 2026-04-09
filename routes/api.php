<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FishTypeController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\AiConsultationController;
use App\Http\Controllers\Api\PasarController;
use App\Http\Controllers\Api\AuthController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/fish-types', [FishTypeController::class, 'index']);
Route::get('/stocks', [App\Http\Controllers\Api\FishTypeController::class, 'getStocks']);
Route::get('/posts', [App\Http\Controllers\Api\FishTypeController::class, 'getPosts']);

Route::get('/forum', [App\Http\Controllers\Api\ForumController::class, 'index']);
Route::post('/forum', [App\Http\Controllers\Api\ForumController::class, 'store']);
Route::post('/forum/{id}/update', [App\Http\Controllers\Api\ForumController::class, 'updatePost']);
Route::delete('/forum/{id}', [App\Http\Controllers\Api\ForumController::class, 'destroy']);
Route::post('/forum/{id}/toggle-like', [App\Http\Controllers\Api\ForumController::class, 'toggleLike']); 
Route::post('/forum/{id}/comment', [App\Http\Controllers\Api\ForumController::class, 'storeComment']);
Route::delete('/forum/comment/{id}', [App\Http\Controllers\Api\ForumController::class, 'destroyComment']);

Route::post('/ai-analyze', [AiConsultationController::class, 'analyze']);

Route::get('/posts', [App\Http\Controllers\Api\PostController::class, 'index']);

Route::get('/pasars', [PasarController::class, 'index']);
Route::post('/pasars', [PasarController::class, 'store']);
Route::delete('/pasars/{id}', [PasarController::class, 'destroy']);
Route::post('/pasars/{id}/update', [PasarController::class, 'update']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/google-login', [AuthController::class, 'googleLogin']);
Route::post('/send-otp-wa', [AuthController::class, 'sendOtpWhatsapp']);
Route::post('/verify-otp-wa', [AuthController::class, 'verifyOtpWhatsapp']);
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profil/update', [AuthController::class, 'updateProfile']);
    Route::post('/ubah-password', [AuthController::class, 'ubahPassword']);
    Route::post('/ubah-kontak', [AuthController::class, 'ubahKontak']);
    Route::delete('/hapus-akun', [AuthController::class, 'hapusAkun']);
});
Route::get('/user/{id}', function($id) {
    $user = \App\Models\User::find($id);
    if($user) {
        return response()->json(['success' => true, 'data' => $user]);
    }
    return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
});
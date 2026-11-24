<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\FishTypeController;
use App\Http\Controllers\Api\ForumController;
use App\Http\Controllers\Api\AiConsultationController;
use App\Http\Controllers\Api\PasarController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\LaporDaruratController;
use App\Http\Controllers\Api\PreOrderController;
use App\Http\Controllers\Api\ExternalInfoController;
use App\Http\Controllers\Api\AsistenChatController;

Route::prefix('info')->middleware('throttle:external-info')->group(function () {
    Route::get('/kkp', [ExternalInfoController::class, 'kkp']);
    Route::get('/cuaca', [ExternalInfoController::class, 'cuaca']);
    Route::get('/tpi', [ExternalInfoController::class, 'tpi']);
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::middleware('throttle:60,1,content-detail:')->group(function () {
        Route::get('/pasars/{id}', [\App\Http\Controllers\Api\ContentDetailController::class, 'pasar']);
        Route::get('/forum/{id}', [\App\Http\Controllers\Api\ContentDetailController::class, 'forum']);
        Route::get('/posts/{id}', [\App\Http\Controllers\Api\ContentDetailController::class, 'berita']);
    });
    Route::get('/search', [\App\Http\Controllers\Api\SearchController::class, 'index'])->middleware('throttle:60,1,omni-search:');
    Route::get('/profiles/{id}', [\App\Http\Controllers\Api\ProfileController::class, 'show'])->whereNumber('id');
    Route::get('/profiles/{id}/pasars', [\App\Http\Controllers\Api\ProfileController::class, 'products'])->whereNumber('id');
Route::get('/fish-types', [FishTypeController::class, 'index']);
Route::get('/stocks', [App\Http\Controllers\Api\FishTypeController::class, 'getStocks']);

Route::get('/forum', [App\Http\Controllers\Api\ForumController::class, 'index']);
Route::post('/forum', [App\Http\Controllers\Api\ForumController::class, 'store']);
Route::post('/forum/{id}/update', [App\Http\Controllers\Api\ForumController::class, 'updatePost']);
Route::delete('/forum/{id}', [App\Http\Controllers\Api\ForumController::class, 'destroy']);
Route::post('/forum/{id}/toggle-like', [App\Http\Controllers\Api\ForumController::class, 'toggleLike']);
Route::post('/forum/{id}/comment', [App\Http\Controllers\Api\ForumController::class, 'storeComment']);
Route::delete('/forum/comment/{id}', [App\Http\Controllers\Api\ForumController::class, 'destroyComment']);
Route::get('/forum/{id}/comments', [App\Http\Controllers\Api\ForumCommentController::class, 'index'])->whereNumber('id');
Route::get('/forum/comment/{id}/replies', [App\Http\Controllers\Api\ForumCommentController::class, 'replies'])->whereNumber('id');
Route::patch('/forum/comment/{id}', [App\Http\Controllers\Api\ForumCommentController::class, 'update'])->whereNumber('id');
Route::put('/forum/comment/{id}/like', [App\Http\Controllers\Api\ForumCommentController::class, 'like'])->whereNumber('id');
Route::delete('/forum/comment/{id}/like', [App\Http\Controllers\Api\ForumCommentController::class, 'unlike'])->whereNumber('id');

Route::post('/ai-analyze', [AiConsultationController::class, 'analyze'])->middleware('throttle:5,1');

Route::get('/posts', [App\Http\Controllers\Api\PostController::class, 'index']);

Route::get('/pasars', [PasarController::class, 'index']);
Route::post('/pasars', [PasarController::class, 'store']);
Route::delete('/pasars/{id}', [PasarController::class, 'destroy']);
Route::post('/pasars/{id}/update', [PasarController::class, 'update']);
});

Route::middleware('throttle:10,1,auth-group:')->group(function () {
Route::post('/register', [AuthController::class, 'register']);
Route::post('/register/send-otp', [\App\Http\Controllers\Api\OtpController::class, 'sendRegistrationOtp']);
Route::post('/register/verify-otp', [\App\Http\Controllers\Api\OtpController::class, 'verifyRegistrationOtp']);
Route::post('/lupa-password/send-otp', [\App\Http\Controllers\Api\OtpController::class, 'sendResetOtp']);
Route::post('/lupa-password/verify', [\App\Http\Controllers\Api\OtpController::class, 'resetPasswordWithOtp']);
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1,auth-login:');
Route::post('/google-login', [AuthController::class, 'googleLogin']);
Route::post('/send-otp-wa', [\App\Http\Controllers\Api\OtpController::class, 'sendOtpWhatsapp']);
Route::post('/verify-otp-wa', [\App\Http\Controllers\Api\OtpController::class, 'verifyOtpWhatsapp']);
});
Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('asisten')->middleware('throttle:asisten-chat')->group(function () {
        Route::post('/chat', [AsistenChatController::class, 'store']);
        Route::get('/chat', [AsistenChatController::class, 'index']);
        Route::get('/chat/{id}', [AsistenChatController::class, 'show'])->whereNumber('id');
    });
    Route::get('/pre-order', [PreOrderController::class, 'index']);
    Route::put('/pasars/{id}/retail-offer', [\App\Http\Controllers\Api\RetailOrderController::class, 'offer'])->whereNumber('id');
    Route::get('/retail-orders', [\App\Http\Controllers\Api\RetailOrderController::class, 'index']);
    Route::post('/retail-orders', [\App\Http\Controllers\Api\RetailOrderController::class, 'store'])->middleware('throttle:20,1');
    Route::put('/retail-orders/{id}/confirmation', [\App\Http\Controllers\Api\RetailOrderController::class, 'confirm'])->whereNumber('id')->middleware('throttle:20,1');
    Route::post('/pre-order', [PreOrderController::class, 'store'])->middleware('throttle:20,1');
    Route::get('/pre-order/{id}', [PreOrderController::class, 'show'])->whereNumber('id');
    Route::put('/pre-order/{id}/confirmation', [PreOrderController::class, 'confirm'])->whereNumber('id')->middleware('throttle:20,1');
    Route::get('/lapor-darurat', [LaporDaruratController::class, 'index']);
    Route::post('/lapor-darurat', [LaporDaruratController::class, 'store']);
    Route::get('/lapor-darurat/{id}', [LaporDaruratController::class, 'show'])->whereNumber('id');
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/profil/update', [AuthController::class, 'updateProfile']);
    Route::post('/ubah-password', [AuthController::class, 'ubahPassword']);
    Route::post('/ubah-kontak', [\App\Http\Controllers\Api\OtpController::class, 'ubahKontak']);
    Route::delete('/hapus-akun', [\App\Http\Controllers\Api\OtpController::class, 'hapusAkun']);
    Route::post('/ubah-kontak/send-otp', [\App\Http\Controllers\Api\OtpController::class, 'sendUbahKontakOtp']);
    Route::post('/hapus-akun/send-otp', [\App\Http\Controllers\Api\OtpController::class, 'sendHapusAkunOtp']);
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
Route::get('/user/{id}', function(Request $request, $id) {
    $user = \App\Models\User::whereKey($request->user()->id)->find($id);
    if($user) {
        return response()->json(['success' => true, 'data' => $user]);
    }
    return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404);
})->middleware('auth:sanctum');
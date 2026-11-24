<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = Notification::where('user_id', $user->id)
            ->orWhereNull('user_id')
            ->latest()
            ->get();

        return response()->json(['success' => true, 'data' => $notifications]);
    }

    public function markAsRead(Request $request, $id)
    {
        $notification = Notification::where('user_id', $request->user()->id)->find($id);
        if (! $notification && Notification::whereNull('user_id')->whereKey($id)->exists()) {
            // Global notifications have no per-user read state in the existing schema.
            return response()->json(['success' => true]);
        }
        if ($notification) {
            $notification->update(['is_read' => true]);
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
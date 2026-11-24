<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileProductResource;
use App\Http\Resources\ProfileResource;
use App\Models\Pasar;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request, string $id)
    {
        $user = User::select(['id', 'name', 'foto_profil', 'fokus_budidaya'])->find($id);
        if (! $user) {
            return $this->notFound();
        }

        return response()->json(['success' => true, 'data' => new ProfileResource($user)])
            ->header('Cache-Control', 'private, no-store');
    }

    public function products(Request $request, string $id)
    {
        $user = User::select('id')->find($id);
        if (! $user) {
            return $this->notFound();
        }

        $request->validate([
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:50',
        ]);
        $page = Pasar::where('user_id', $user->id)->with('user:id,name,foto_profil')
            ->latest()->orderByDesc('id')->paginate((int) $request->input('per_page', 20));

        return response()->json([
            'success' => true,
            'data' => ProfileProductResource::collection($page->getCollection()),
            'meta' => [
                'current_page' => $page->currentPage(), 'per_page' => $page->perPage(),
                'total' => $page->total(), 'last_page' => $page->lastPage(),
            ],
        ])->header('Cache-Control', 'private, no-store');
    }

    private function notFound()
    {
        return response()->json(['success' => false, 'message' => 'User tidak ditemukan'], 404)
            ->header('Cache-Control', 'private, no-store');
    }
}

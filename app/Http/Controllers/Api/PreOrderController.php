<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PreOrder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PreOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        Gate::authorize('create', PreOrder::class);

        $data = $request->validate([
            'pasar_id' => ['required', 'integer', Rule::exists('pasars', 'id')->where('tipe_penjualan', 'grosir')],
            'total_tonase' => ['required', 'numeric', 'gt:0', 'max:99999999.99', 'decimal:0,2'],
            'tgl_pengambilan' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'status_bayar' => ['missing'],
            'user_id' => ['missing'],
            'confirmed_at' => ['missing'],
        ]);

        abort_if(\App\Models\Pasar::whereKey($data['pasar_id'])->where('user_id', $request->user()->id)->exists(), 422, 'Tidak dapat memesan produk sendiri.');
        $order = new PreOrder($data);
        $order->user()->associate($request->user());
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Pre-order berhasil dibuat',
            'data' => $order,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        Gate::authorize('viewHistory', PreOrder::class);

        return response()->json([
            'success' => true,
            'message' => 'Riwayat pre-order berhasil diambil',
            'data' => PreOrder::forParticipant($request->user())
                ->latest()->orderByDesc('id')->paginate(20),
        ]);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        // Scope before lookup, including admins: no mobile access to unrelated orders.
        $order = PreOrder::forParticipant($request->user())->find($id);
        if ($order === null) {
            return response()->json(['message' => 'Not Found'], 404);
        }

        Gate::authorize('view', $order);
        $order->setAttribute('can_confirm', !$order->confirmed_at && $order->user_id != $request->user()->id && $order->pasar()->where('user_id', $request->user()->id)->exists());

        return response()->json([
            'success' => true,
            'message' => 'Detail pre-order berhasil diambil',
            'data' => $order,
        ]);
    }

    public function confirm(Request $request, string $id): JsonResponse
    {
        abort_if($request->all() !== [], 422, 'Konfirmasi tidak menerima parameter.');
        $query = PreOrder::whereKey($id)->where('user_id', '!=', $request->user()->id)
            ->whereHas('pasar', fn ($q) => $q->where('user_id', $request->user()->id));
        $order = (clone $query)->firstOrFail();
        (clone $query)->whereNull('confirmed_at')->update(['confirmed_at' => now()]);
        return response()->json(['success' => true, 'data' => $order->refresh()->setAttribute('can_confirm', false)]);
    }
}
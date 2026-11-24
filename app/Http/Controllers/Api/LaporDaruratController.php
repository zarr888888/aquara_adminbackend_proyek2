<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LaporDarurat;
use App\Traits\UploadsToCloudinary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class LaporDaruratController extends Controller
{
    use UploadsToCloudinary;

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Riwayat laporan darurat berhasil diambil',
            'data' => LaporDarurat::where('user_id', $request->user()->id)
                ->latest()->orderByDesc('id')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'jenis_sos' => ['required', 'string', Rule::in(array_keys(LaporDarurat::JENIS_SOS_OPTIONS))],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:2048'],
        ]);

        $foto = null;
        if ($request->hasFile('foto')) {
            $foto = $this->uploadFotoToCloudinary($request->file('foto'), 'samudera/sos-fotos');

            if (! $foto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Foto gagal diunggah. Silakan kirim ulang laporan.',
                ], 503);
            }
        }

        try {
            $laporan = LaporDarurat::create([
                'user_id' => $request->user()->id,
                'jenis_sos' => $data['jenis_sos'],
                'deskripsi' => $data['deskripsi'],
                'foto' => $foto,
                'status_laporan' => 'Pending',
                'admin_id' => null,
            ]);
        } catch (Throwable $exception) {
            $this->deleteFotoFromCloudinary($foto);
            throw $exception;
        }

        return response()->json([
            'success' => true,
            'message' => 'Laporan darurat berhasil dikirim',
            'data' => $laporan,
        ], 201);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        $laporan = LaporDarurat::where('user_id', $request->user()->id)->findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail laporan darurat berhasil diambil',
            'data' => $laporan,
        ]);
    }
}
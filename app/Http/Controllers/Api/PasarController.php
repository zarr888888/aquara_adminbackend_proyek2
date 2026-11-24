<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProfileProductResource;
use App\Models\Pasar;
use App\Traits\UploadsToCloudinary;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PasarController extends Controller
{
    use UploadsToCloudinary;


    public function index()
    {
        $pasars = Pasar::with('user:id,name,foto_profil')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Jualan Pasar Berhasil Diambil',
            'data' => ProfileProductResource::collection($pasars),
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_ikan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0|max:999999999',
            'deskripsi' => 'required|string|max:2000',
            'nomor_wa' => 'required|string|max:20',
            'lokasi' => 'required|string|max:255',
            'stok' => 'nullable|integer|min:0|max:999999999',
            'kategori' => ['nullable', 'string', Rule::in(array_keys(Pasar::KATEGORI_OPTIONS))],
            'tipe_penjualan' => ['nullable', 'string', Rule::in(array_keys(Pasar::TIPE_PENJUALAN_OPTIONS))],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $this->uploadFotoToCloudinary($request->file('foto'), 'samudera/pasar-fotos');
        }

        $pasar = \App\Models\Pasar::create([
            'user_id' => $request->user()->id,
            'nama_ikan' => $request->nama_ikan,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'nomor_wa' => $request->nomor_wa,
            'lokasi' => $request->lokasi,
            'foto' => $fotoPath,
            'stok' => $request->input('stok', 0),
            'kategori' => $request->input('kategori', 'tawar'),
            'tipe_penjualan' => $request->input('tipe_penjualan', 'eceran'),
        ]);

                \App\Models\Notification::create([
            'user_id' => null,
            'title' => '📢 Info Pasar SAMUDERA Terbaru!',
            'message' => "Ada {$pasar->nama_ikan} nih! Harga Rp " . number_format($pasar->harga, 0, ',', '.') . ". Yuk cek!",
            'type' => 'pasar',
            'reference_id' => $pasar->id
        ]);

        // PELATUK FIREBASE: TEMBAKKAN NOTIFIKASI KE SEMUA HP!
        try {
            $firebase = (new \Kreait\Firebase\Factory)
                ->withServiceAccount(storage_path('app/firebase-auth.json'));
            
            $messaging = $firebase->createMessaging();
            $hargaFormat = number_format($pasar->harga, 0, ',', '.');

            $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                ->withNotification(\Kreait\Firebase\Messaging\Notification::create(
                    '📢 Info Pasar SAMUDERA Terbaru!',
                    "Ada {$pasar->nama_ikan} nih! Harga Rp {$hargaFormat}. Yuk cek sekarang di aplikasi!"
                ))
                ->withData(['type' => 'pasar'])->toTopic('info_pasar');

            $messaging->send($message);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('FCM Error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => 'Jualan berhasil ditambahkan',
            'data' => $pasar
        ], 201);
    }

    public function destroy(Request $request, $id)
    {
        $pasar = \App\Models\Pasar::find($id);
        
        if (!$pasar) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($pasar->foto) {
            abort_unless((int) $pasar->user_id === (int) $request->user()->id, 403);
            $this->deleteFotoFromCloudinary($pasar->foto);
        }

        abort_unless((int) $pasar->user_id === (int) $request->user()->id, 403);
        \App\Models\Notification::where('type', 'pasar')->where('reference_id', $id)->delete();

        $pasar->delete();

        return response()->json(['message' => 'Data jualan berhasil dihapus'], 200);
    }

    public function update(Request $request, $id)
    {
        $pasar = \App\Models\Pasar::find($id);
        if ($pasar) {
            abort_unless((int) $pasar->user_id === (int) $request->user()->id, 403);
        }
        
        if (!$pasar) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_ikan' => 'required|string|max:255',
            'harga' => 'required|numeric|min:0|max:999999999',
            'deskripsi' => 'required|string|max:2000',
            'nomor_wa' => 'required|string|max:20',
            'lokasi' => 'required|string|max:255',
            'stok' => 'nullable|integer|min:0|max:999999999',
            'kategori' => ['nullable', 'string', Rule::in(array_keys(Pasar::KATEGORI_OPTIONS))],
            'tipe_penjualan' => ['nullable', 'string', Rule::in(array_keys(Pasar::TIPE_PENJUALAN_OPTIONS))],
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('foto')) {
            if ($pasar->foto) {
                $this->deleteFotoFromCloudinary($pasar->foto);
            }
            $pasar->foto = $this->uploadFotoToCloudinary($request->file('foto'), 'samudera/pasar-fotos');
        }

        $pasar->nama_ikan = $request->nama_ikan;
        $pasar->harga = $request->harga;
        $pasar->deskripsi = $request->deskripsi;
        $pasar->nomor_wa = $request->nomor_wa;
        $pasar->lokasi = $request->lokasi;

        if ($request->filled('stok')) {
            $pasar->stok = $request->stok;
        }
        if ($request->filled('kategori')) {
            $pasar->kategori = $request->kategori;
        }
        if ($request->filled('tipe_penjualan')) {
            if ($pasar->tipe_penjualan !== $request->tipe_penjualan) {
                $pasar->satuan_jual = null;
            }
            $pasar->tipe_penjualan = $request->tipe_penjualan;
        }

        $pasar->save();

        return response()->json([
            'success' => true,
            'message' => 'Jualan berhasil diperbarui',
            'data' => $pasar
        ], 200);
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasar;
use Illuminate\Http\Request;

class PasarController extends Controller
{
    public function index()
    {
        $pasars = Pasar::with('user')->latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Jualan Pasar Berhasil Diambil',
            'data' => $pasars
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required',
            'nama_ikan' => 'required',
            'harga' => 'required|numeric',
            'deskripsi' => 'required',
            'nomor_wa' => 'required',
            'lokasi' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('pasar-fotos', 'public');
        }

        $pasar = \App\Models\Pasar::create([
            'user_id' => $request->user_id,
            'nama_ikan' => $request->nama_ikan,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'nomor_wa' => $request->nomor_wa,
            'lokasi' => $request->lokasi,
            'foto' => $fotoPath,
        ]);

                \App\Models\Notification::create([
            'user_id' => null,
            'title' => '📢 Info Pasar AQUARA Terbaru!',
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
                    '📢 Info Pasar AQUARA Terbaru!',
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

    public function destroy($id)
    {
        $pasar = \App\Models\Pasar::find($id);
        
        if (!$pasar) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        if ($pasar->foto) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($pasar->foto);
        }

        \App\Models\Notification::where('type', 'pasar')->where('reference_id', $id)->delete();

        $pasar->delete();

        return response()->json(['message' => 'Data jualan berhasil dihapus'], 200);
    }

    public function update(Request $request, $id)
    {
        $pasar = \App\Models\Pasar::find($id);
        
        if (!$pasar) {
            return response()->json(['success' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $request->validate([
            'nama_ikan' => 'required',
            'harga' => 'required|numeric',
            'deskripsi' => 'required',
            'nomor_wa' => 'required',
            'lokasi' => 'required',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048'
        ]);

        if ($request->hasFile('foto')) {
            if ($pasar->foto) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($pasar->foto);
            }
            $pasar->foto = $request->file('foto')->store('pasar-fotos', 'public');
        }

        $pasar->nama_ikan = $request->nama_ikan;
        $pasar->harga = $request->harga;
        $pasar->deskripsi = $request->deskripsi;
        $pasar->nomor_wa = $request->nomor_wa;
        $pasar->lokasi = $request->lokasi;
        
        $pasar->save();

        return response()->json([
            'success' => true,
            'message' => 'Jualan berhasil diperbarui',
            'data' => $pasar
        ], 200);
    }
}
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

        $pasar = Pasar::create([
            'user_id' => $request->user_id,
            'nama_ikan' => $request->nama_ikan,
            'harga' => $request->harga,
            'deskripsi' => $request->deskripsi,
            'nomor_wa' => $request->nomor_wa,
            'lokasi' => $request->lokasi,
            'foto' => $fotoPath,
        ]);

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
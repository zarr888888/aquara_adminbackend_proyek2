<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'nama_ikan' => $this->nama_ikan,
            'deskripsi' => $this->deskripsi,
            'harga' => $this->harga,
            'satuan_jual' => $this->satuan_jual,
            'foto' => $this->foto,
            'nomor_wa' => $this->nomor_wa,
            'lokasi' => $this->lokasi,
            'stok' => $this->stok,
            'kategori' => $this->kategori,
            'tipe_penjualan' => $this->tipe_penjualan,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'user' => ProfileResource::author($this->resource, $request),
        ];
    }
}

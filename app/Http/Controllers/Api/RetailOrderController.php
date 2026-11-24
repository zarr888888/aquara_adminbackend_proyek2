<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pasar;
use App\Models\RetailOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RetailOrderController extends Controller
{
    private function fields(Request $request, array $rules): array
    {
        abort_if(array_diff(array_keys($request->all()), array_keys($rules)) !== [], 422, 'Parameter tidak dikenal.');
        return $request->validate($rules);
    }

    public function offer(Request $request, int $id)
    {
        $data = $this->fields($request, ['satuan_jual'=>'required|in:kg,ekor,kemasan','harga'=>'required|integer|between:1,999999999']);
        return DB::transaction(function () use ($request,$id,$data) {
            $product = Pasar::where('user_id',$request->user()->id)->lockForUpdate()->findOrFail($id);
            abort_unless($product->tipe_penjualan === 'eceran', 422);
            $product->satuan_jual = $data['satuan_jual'];
            $product->harga = $data['harga'];
            $product->save();
            return response()->json(['success'=>true,'data'=>$product]);
        });
    }

    public function store(Request $request)
    {
        $data = $this->fields($request, ['pasar_id'=>'required|integer|min:1','jumlah'=>['required','string','regex:/^(?:0|[1-9]\d{0,5})(?:\.\d{1,3})?$/D'],
            'expected_unit'=>'required|in:kg,ekor,kemasan','expected_price'=>'required|integer|between:1,999999999']);
        return DB::transaction(function () use ($data,$request) {
            $p = Pasar::lockForUpdate()->findOrFail($data['pasar_id']);
            abort_if($p->user_id == $request->user()->id, 403);
            abort_unless($p->tipe_penjualan === 'eceran' && $p->satuan_jual === $data['expected_unit'] && $p->harga === (int)$data['expected_price'], 409, 'Penawaran berubah. Muat ulang Pasar.');
            $parts = explode('.', $data['jumlah']);
            abort_if($p->satuan_jual !== 'kg' && count($parts) !== 1, 422, 'Jumlah harus bulat.');
            $scaled = 1000*(int)$parts[0] + (int) str_pad($parts[1] ?? '', 3,'0');
            abort_if($scaled === 0, 422);
            $total = intdiv($scaled*$p->harga + 500, 1000);
            abort_if($total < 1, 422, 'Total kurang dari satu rupiah.');
            $order = new RetailOrder();
            $order->forceFill(['buyer_id'=>$request->user()->id,'seller_id'=>$p->user_id,'pasar_id'=>$p->id,'nama_ikan'=>$p->nama_ikan,
                'satuan_jual'=>$p->satuan_jual,'harga_satuan'=>$p->harga,'jumlah'=>$data['jumlah'],'total_harga'=>$total])->save();
            return response()->json(['success'=>true,'data'=>$order->refresh()],201);
        });
    }

    public function index(Request $request)
    {
        $request->validate(['page'=>'sometimes|integer|between:1,1000000']);
        $id = $request->user()->id;
        return response()->json(['success'=>true,'data'=>RetailOrder::where('buyer_id',$id)->orWhere('seller_id',$id)->orderByDesc('id')->paginate(20)]);
    }

    public function confirm(Request $request, int $id)
    {
        $this->fields($request, []);
        $query = RetailOrder::where('seller_id',$request->user()->id)->whereKey($id);
        (clone $query)->whereNull('confirmed_at')->update(['confirmed_at'=>now()]);
        return response()->json(['success'=>true,'data'=>$query->firstOrFail()]);
    }
}
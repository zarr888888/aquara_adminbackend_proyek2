<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\BmkgForecast;
use App\Support\ExternalInfo;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExternalInfoController extends Controller
{
    public function kkp(ExternalInfo $info): JsonResponse
    {
        return response()->json($info->get('kkp'));
    }

    public function cuaca(Request $request, BmkgForecast $forecast): JsonResponse
    {
        $query = validator($request->query(), ['kode' => ['sometimes', 'bail', 'required', 'string', Rule::in(array_keys(BmkgForecast::PORTS))]])->validate();
        $payload = $forecast->get($query['kode'] ?? BmkgForecast::DEFAULT_PORT);
        return response()->json($payload, $payload['success'] ? 200 : 503);
    }

    public function tpi(ExternalInfo $info): JsonResponse
    {
        return response()->json($info->get('tpi'));
    }
}
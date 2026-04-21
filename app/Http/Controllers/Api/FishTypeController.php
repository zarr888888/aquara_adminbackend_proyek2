<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FishType;
use Illuminate\Http\Request;
use App\Models\Stock; 
use App\Models\Post;

class FishTypeController extends Controller
{
    public function index()
    {
        $fishTypes = FishType::all();
        
        return response()->json([
            'status' => 'success',
            'data' => $fishTypes
        ], 200);
    }

    public function getStocks()
    {
        $stocks = Stock::with('fishType')->latest()->get();
    
        return response()->json([
            'success' => true,
            'data' => $stocks
        ]);
    }

        public function getPosts()
    {
        $posts = Post::latest()->get();
        
        return response()->json([
            'success' => true,
            'data' => $posts
        ]);
    }
}
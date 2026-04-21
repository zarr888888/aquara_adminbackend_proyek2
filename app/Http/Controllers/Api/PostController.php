<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Berita Berhasil Diambil',
            'data'    => $posts
        ], 200);
    }
}
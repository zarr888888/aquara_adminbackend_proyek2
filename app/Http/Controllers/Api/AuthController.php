<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|nullable',
            'phone' => 'required|string|unique:users|nullable',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendaftar!',
            'data' => $user,
            'token' => $token
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'login_id' => 'required|string', 
            'password' => 'required|string',
        ]);

        $loginType = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$loginType => $request->login_id, 'password' => $request->password])) {
            
            $user = User::where($loginType, $request->login_id)->first();
            
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'success' => true,
                'message' => 'Login Berhasil!',
                'data' => $user,
                'token' => $token
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Email/No HP atau Password salah!'
        ], 401);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Berhasil Logout'
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'fokus_budidaya' => 'nullable|string',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' 
        ]);

        $user->name = $request->name;
        if ($request->filled('fokus_budidaya')) {
            $user->fokus_budidaya = $request->fokus_budidaya;
        }

        if ($request->hasFile('foto_profil')) {
            $fotoPath = $request->file('foto_profil')->store('profil-fotos', 'public');
            $user->foto_profil = $fotoPath;
        }

        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui!',
            'data' => $user
        ]);
    }

    public function ubahPassword(Request $request)
    {
        $request->validate([
            'old_password' => 'required',
            'password' => 'required|min:6|confirmed', 
        ]);

        $user = $request->user();

        if (!Hash::check($request->old_password, $user->password)) {
            return response()->json([
                'success' => false, 
                'message' => 'Kata sandi lama yang Anda masukkan salah.'
            ], 400); 
        }

        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'success' => true, 
            'message' => 'Kata sandi berhasil diperbarui!'
        ], 200);
    }

    public function ubahKontak(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'email' => 'nullable|email|unique:users,email,' . $user->id,
            'phone' => 'required|unique:users,phone,' . $user->id,
        ]);

        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return response()->json([
            'success' => true, 
            'message' => 'Email dan Nomor HP berhasil diperbarui!'
        ], 200);
    }

    public function hapusAkun(Request $request)
    {
        $user = $request->user();
        
        $user->tokens()->delete();
        
        $user->delete();

        return response()->json([
            'success' => true, 
            'message' => 'Akun berhasil dihapus permanen.'
        ], 200);
    }
}
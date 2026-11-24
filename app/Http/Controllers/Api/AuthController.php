<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    use \App\Traits\UploadsToCloudinary;
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'nullable|string|email|unique:users,email', 
            'phone'    => 'required|string|min:10|max:15|unique:users,phone',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            // email_verified_at otomatis null, menandakan belum verifikasi
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendaftar! Silakan lakukan verifikasi OTP.',
            'data' => $user
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
            
            if (is_null($user->email_verified_at)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda belum diverifikasi OTP. Silakan verifikasi terlebih dahulu.'
                ], 403);
            }
            
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
            'fokus_budidaya' => 'nullable|string|max:255',
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048' 
        ]);

        $user->name = $request->name;
        if ($request->filled('fokus_budidaya')) {
            $user->fokus_budidaya = $request->fokus_budidaya;
        }

        if ($request->hasFile('foto_profil')) {
            $fotoPath = $this->uploadFotoToCloudinary($request->file('foto_profil'), 'samudera/profil-fotos');
            abort_unless($fotoPath, 503, 'Upload foto gagal.');
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

    public function googleLogin(Request $request)
    {
        $request->validate([
            'id_token' => 'required|string|max:10000',
        ]);

        abort_unless(config('services.google.client_id'), 503, 'Login Google belum dikonfigurasi.');
        try {
            $claims = app(\Google\Auth\AccessToken::class)->verify($request->id_token, [
                'audience' => config('services.google.client_id'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Verifikasi Google gagal.'], 401);
        }
        abort_unless($claims && in_array($claims['iss'] ?? '', ['accounts.google.com', 'https://accounts.google.com'], true)
            && ($claims['email_verified'] ?? false) === true && ! empty($claims['sub'])
            && filter_var($claims['email'] ?? '', FILTER_VALIDATE_EMAIL), 401);
        $request->merge([
            'email' => $claims['email'],
            'name' => $claims['name'] ?? 'Pengguna Google',
            'google_id' => $claims['sub'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'google_id' => $request->google_id,
                'password'  => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)), 
                'phone'     => 'GL' . time(), 
            ]);
            
            $user->email_verified_at = now();
            $user->save();
        } else {
            abort_if(! empty($user->google_id) && $user->google_id !== $claims['sub'], 403);
            if (empty($user->google_id)) {
                $user->update(['google_id' => $request->google_id]);
            }
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login Google Berhasil!',
            'data' => $user,
            'token' => $token
        ], 200);
    }

}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AuthController extends Controller
{
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

    public function googleLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'name' => 'required|string',
            'google_id' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'google_id' => $request->google_id,
                'password'  => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(24)), 
                'phone'     => 'GGL-' . time() . rand(10, 99),
            ]);
        } else {
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

    // FUNGSI KIRIM OTP WHATSAPP 
    public function sendOtpWhatsapp(Request $request)
    {
        try {
            $request->validate(['phone' => 'required']);
            $phone = $request->phone;

            $user = User::where('phone', $phone)->first();

            if (!$user) {
                $user = new User(); 
                $user->name = 'Pengguna AQUARA';
                $user->phone = $phone;
                $user->email = 'wa_' . time() . '@aquara.com';
                $user->password = Hash::make(Str::random(24));
                $user->save();
            }

            $otp = rand(100000, 999999);

            $user->otp = $otp;
            $user->otp_expired_at = now()->addMinutes(5);
            $user->save();

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'https://api.fonnte.com/send',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => array(
                    'target' => $phone,
                    'message' => "*AQUARA LOGIN*\n\nKode OTP Anda adalah: *$otp*\n\nKode ini berlaku selama 5 menit.",
                ),
                CURLOPT_HTTPHEADER => array(
                    'Authorization: fRRqe1s757GduhxXCMfh' //
                ),
            ));

            curl_exec($curl);
            curl_close($curl);

            return response()->json([
                'success' => true,
                'message' => 'Kode OTP berhasil dikirim ke WhatsApp!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan di server: ' . $e->getMessage()
            ], 500);
        }
    }

    // FUNGSI VERIFIKASI OTP WHATSAPP
    public function verifyOtpWhatsapp(Request $request)
    {
        $request->validate([
            'phone' => 'required',
            'otp' => 'required'
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || (string)$user->otp !== (string)$request->otp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah. Silakan periksa kembali.'
            ], 401);
        }

        if (now()->greaterThan($user->otp_expired_at)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kadaluarsa. Silakan minta kode baru.'
            ], 401);
        }

        $user->otp = null;
        $user->otp_expired_at = null;
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login WhatsApp Berhasil!',
            'data' => $user,
            'token' => $token
        ], 200);
    }
}
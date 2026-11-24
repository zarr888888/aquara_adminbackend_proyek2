<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OtpController extends Controller
{
    public function __construct(private OtpService $otp) {}

    private function account(Request $request): User
    {
        $request->validate(['login_id' => 'required|string|max:255']);
        $field = filter_var($request->login_id, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        return User::where($field, $request->login_id)->firstOrFail();
    }

    private function code(Request $request): string
    {
        return $request->validate(['otp' => ['required', 'string', 'regex:/^[0-9]{6}$/']])['otp'];
    }

    private function send(Request $request, User $user, string $purpose)
    {
        $request->validate(['method' => 'required|in:email,wa']);
        $target = $request->method === 'email' ? $user->email : $user->phone;
        $this->otp->issue($user, $purpose, $request->method, $target ?? '');

        return response()->json(['success' => true, 'message' => 'OTP dikirim. Berlaku 5 menit.']);
    }

    private function token(User $user)
    {
        return response()->json(['success' => true, 'data' => $user,
            'token' => $user->createToken('auth_token')->plainTextToken]);
    }

    public function sendRegistrationOtp(Request $request)
    {
        return $this->send($request, $this->account($request), 'register');
    }

    public function verifyRegistrationOtp(Request $request)
    {
        return $this->otp->consume($this->account($request), 'register', $this->code($request), function (User $user) {
            $user->email_verified_at = now();
            $user->save();

            return $this->token($user);
        });
    }

    public function sendOtpWhatsapp(Request $request)
    {
        $request->validate(['phone' => ['required', 'string', 'regex:/^\+?[0-9]{10,15}$/']]);
        $user = User::firstOrCreate(['phone' => $request->phone], [
            'name' => 'Pengguna SAMUDERA', 'email' => 'wa_'.Str::uuid().'@samudera.invalid',
            'password' => Hash::make(Str::random(64)),
        ]);
        $this->otp->issue($user, 'login', 'wa', $user->phone);

        return response()->json(['success' => true, 'message' => 'OTP login dikirim.']);
    }

    public function verifyOtpWhatsapp(Request $request)
    {
        $request->validate(['phone' => 'required|string|max:16']);
        $user = User::where('phone', $request->phone)->firstOrFail();

        return $this->otp->consume($user, 'login', $this->code($request), fn (User $user) => $this->token($user), $user->phone, 'wa');
    }

    public function sendResetOtp(Request $request)
    {
        return $this->send($request, $this->account($request), 'reset');
    }

    public function resetPasswordWithOtp(Request $request)
    {
        $request->validate(['new_password' => 'required|string|min:12|max:128']);

        return $this->otp->consume($this->account($request), 'reset', $this->code($request), function (User $user) use ($request) {
            $user->password = Hash::make($request->new_password);
            $user->save();
            $user->tokens()->delete();

            return response()->json(['success' => true, 'message' => 'Password diperbarui. Silakan login kembali.']);
        });
    }

    public function sendUbahKontakOtp(Request $request)
    {
        $request->validate(['method' => 'required|in:email,wa', 'target_contact' => 'required|string|max:255']);
        $column = $request->method === 'email' ? 'email' : 'phone';
        $request->validate(['target_contact' => [Rule::unique('users', $column)->ignore($request->user()->id)]]);
        $this->otp->issue($request->user(), 'contact', $request->method, $request->target_contact);

        return response()->json(['success' => true, 'message' => 'OTP dikirim ke kontak baru.']);
    }

    public function ubahKontak(Request $request)
    {
        $request->validate(['method' => 'required|in:email,wa', 'target_contact' => 'required|string|max:255',
            'email' => 'missing', 'phone' => 'missing']);
        $column = $request->method === 'email' ? 'email' : 'phone';
        $request->validate(['target_contact' => [Rule::unique('users', $column)->ignore($request->user()->id)]]);

        return $this->otp->consume($request->user(), 'contact', $this->code($request), function (User $user) use ($request, $column) {
            $user->{$column} = $request->target_contact;
            $user->save();
            $user->tokens()->delete();

            return response()->json(['success' => true, 'message' => 'Kontak terverifikasi diperbarui. Silakan login kembali.']);
        }, $request->target_contact, $request->method);
    }

    public function sendHapusAkunOtp(Request $request)
    {
        return $this->send($request, $request->user(), 'delete');
    }

    public function hapusAkun(Request $request)
    {
        return $this->otp->consume($request->user(), 'delete', $this->code($request), function (User $user) {
            $user->tokens()->delete();
            $user->delete();

            return response()->json(['success' => true, 'message' => 'Akun dihapus.']);
        });
    }
}
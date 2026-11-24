<?php

namespace App\Support;

use App\Models\User;
use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class OtpService
{
    public const PURPOSES = ['register', 'login', 'reset', 'contact', 'delete'];

    public function issue(User $user, string $purpose, string $channel, string $target): void
    {
        abort_unless(in_array($purpose, self::PURPOSES, true) && in_array($channel, ['email', 'wa'], true), 422);
        validator(['target' => $target], ['target' => $channel === 'email'
            ? 'required|email|max:255' : ['required', 'regex:/^\+?[0-9]{10,15}$/']])->validate();

        [$code, $hash] = DB::transaction(function () use ($user, $purpose, $channel, $target) {
            User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $old = DB::table('otp_challenges')->where('user_id', $user->id)->first();
            $activeWindow = $old && Carbon::parse($old->window_started_at)->addMinutes(15)->isFuture();
            abort_if($activeWindow && ($old->attempts >= 5 || $old->send_count >= 3), 429, 'Batas OTP akun tercapai. Tunggu 15 menit.');
            abort_if($old && Carbon::parse($old->sent_at)->addMinute()->isFuture(), 429, 'Tunggu sebelum meminta OTP lagi.');
            $code = (string) random_int(100000, 999999);
            $hash = Hash::make($code);
            DB::table('otp_challenges')->updateOrInsert(['user_id' => $user->id], [
                'purpose' => $purpose, 'target' => $target, 'channel' => $channel,
                'code_hash' => $hash, 'expires_at' => now()->addMinutes(5), 'consumed_at' => null,
                'attempts' => $activeWindow ? $old->attempts : 0,
                'send_count' => $activeWindow ? $old->send_count + 1 : 1,
                'window_started_at' => $activeWindow ? $old->window_started_at : now(),
                'sent_at' => now(), 'created_at' => $old?->created_at ?? now(), 'updated_at' => now(),
            ]);
            // Legacy plaintext challenges are no longer accepted.
            DB::table('users')->where('id', $user->id)->update(['otp' => null, 'otp_expired_at' => null]);

            return [$code, $hash];
        });

        try {
            $this->deliver($channel, $target, $code, $purpose);
        } catch (\Throwable $exception) {
            DB::table('otp_challenges')->where('user_id', $user->id)->where('code_hash', $hash)
                ->update(['consumed_at' => now()]);
            abort(503, 'Pengiriman OTP gagal. Silakan coba kembali nanti.');
        }
    }

    public function consume(User $user, string $purpose, string $code, Closure $action, ?string $target = null, ?string $channel = null): mixed
    {
        $result = DB::transaction(function () use ($user, $purpose, $code, $action, $target, $channel) {
            $lockedUser = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            $otp = DB::table('otp_challenges')->where('user_id', $user->id)->first();
            if (! $otp) {
                return ['error' => 401];
            }
            if ($otp->attempts >= 5 && Carbon::parse($otp->window_started_at)->addMinutes(15)->isFuture()) {
                return ['error' => 429];
            }
            $valid = ! $otp->consumed_at && Carbon::parse($otp->expires_at)->isFuture()
                && $otp->attempts < 5 && $otp->purpose === $purpose
                && ($target === null || $otp->target === $target)
                && ($channel === null || $otp->channel === $channel)
                && Hash::check($code, $otp->code_hash);
            // Existing-account operations must still refer to the currently registered contact.
            if ($valid && $purpose !== 'contact') {
                $valid = $otp->target === ($otp->channel === 'email' ? $lockedUser->email : $lockedUser->phone);
            }
            if (! $valid) {
                DB::table('otp_challenges')->where('id', $otp->id)->increment('attempts');

                return ['error' => $otp->attempts + 1 >= 5 ? 429 : 401];
            }
            DB::table('otp_challenges')->where('id', $otp->id)->update(['consumed_at' => now()]);

            return ['value' => $action($lockedUser, $otp)];
        });
        // Throw after commit so failed-attempt counters cannot roll back.
        abort_if(isset($result['error']), $result['error'] ?? 401, 'OTP tidak valid, kedaluwarsa, atau batas percobaan tercapai.');

        return $result['value'];
    }

    protected function deliver(string $channel, string $target, string $code, string $purpose): void
    {
        $text = "SAMUDERA — OTP {$purpose}: {$code}. Berlaku 5 menit, sekali pakai. Jangan bagikan kode ini.";
        if ($channel === 'email') {
            Mail::raw($text, fn ($mail) => $mail->to($target)->subject('OTP SAMUDERA: '.$purpose));

            return;
        }
        if (! config('services.fonnte.token')) {
            throw ValidationException::withMessages(['method' => 'Layanan WA belum dikonfigurasi.']);
        }
        $response = Http::connectTimeout(5)->timeout(15)->withHeaders(['Authorization' => config('services.fonnte.token')])
            ->asForm()->post('https://api.fonnte.com/send', ['target' => $target, 'message' => $text]);
        if (! $response->successful() || $response->json('status') !== true) {
            throw new \RuntimeException('OTP delivery failed.');
        }
    }
}
<?php

namespace App\Support;

class AsistenReply
{
    public const REQUESTS_PER_MINUTE = 10;

    public function reply(string $message): string
    {
        $message = mb_strtolower($message);
        $prefix = '[Asisten SAMUDERA — simulasi] ';

        // Fixed responses only: never execute instructions or interpolate user input.
        return $prefix.match (true) {
            str_contains($message, 'sos'), str_contains($message, 'darurat') =>
                'Gunakan fitur SOS Bahari untuk mengirim laporan beserta deskripsi dan foto. Chat ini bukan layanan tanggap darurat.',
            str_contains($message, 'grosir'), str_contains($message, 'pre-order') =>
                'Pilih komoditas grosir, isi tonase dan tanggal pengambilan melalui fitur Bursa Grosir.',
            str_contains($message, 'cuaca') =>
                'Informasi cuaca di SAMUDERA saat ini masih simulasi. Gunakan informasi resmi BMKG untuk keputusan keselamatan.',
            default => 'Saya dapat membantu menjelaskan fitur SOS Bahari, Bursa Grosir, dan informasi cuaca. Jangan kirim password, OTP, atau informasi rahasia.',
        };
    }
}
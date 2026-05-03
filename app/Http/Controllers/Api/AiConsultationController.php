<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AiConsultation;
use Illuminate\Support\Facades\Http;

class AiConsultationController extends Controller
{
    public function analyze(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg|max:15360',
        ]);

        $imagePath = $request->file('image')->store('consultations', 'public');

        $imagePathAsli = $request->file('image')->path();
        $base64Image = base64_encode(file_get_contents($imagePathAsli));
        $mimeType = $request->file('image')->getMimeType();

        $prompt = "Kamu adalah Pakar Perikanan Air Tawar di Dinas Perikanan Indramayu. 
        Analisa foto ini. Jika ini BUKAN foto ikan atau air kolam/tambak, balas dengan Penyakit: 'Tidak Terdeteksi', Kualitas Air: 'Tidak Terdeteksi', Solusi: 'Maaf, gambar tidak valid. Harap unggah foto ikan atau air kolam yang jelas.'
        Jika ini foto air kolam, fokus pada analisa kualitas air dan berikan solusi untuk memperbaiki kualitas air tersebut, bagian penyakit isi dengan 'Tidak ada ikan terdeteksi'.
        Jika ini foto ikan sakit, sebutkan dugaan penyakitnya, kondisi air (jika terlihat/relevan), dan solusi pengobatannya.
        
        WAJIB kembalikan HANYA dalam format JSON persis seperti ini, tanpa format markdown, tanpa backtick:
        {
            \"disease_detected\": \"Nama Penyakit atau Tidak Terdeteksi\",
            \"water_quality_status\": \"Status Air atau Tidak Terdeteksi\",
            \"solution\": \"Langkah-langkah solusi penanganan di sini\"
        }";
        $apiKey = config('services.gemini.key');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=' . $apiKey;

        $response = Http::withoutVerifying()->withHeaders([
            'Content-Type' => 'application/json',
        ])->post($url, [
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Image
                            ]
                        ]
                    ]
                ]
            ]
        ]);

        if ($response->successful()) {
            $geminiData = $response->json();
            
            $aiTextResponse = $geminiData['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            $aiTextResponse = str_replace(['```json', '```'], '', $aiTextResponse);
            $aiTextResponse = trim($aiTextResponse);

            $hasilAnalisa = json_decode($aiTextResponse, true);

            $disease = $hasilAnalisa['disease_detected'] ?? 'Gagal mendeteksi penyakit';
            $water = $hasilAnalisa['water_quality_status'] ?? 'Gagal mendeteksi kualitas air';
            $solution = $hasilAnalisa['solution'] ?? 'Gagal menyusun solusi';

            $consultation = AiConsultation::create([
                'user_id' => $request->user() ? $request->user()->id : null,
                'image_path' => $imagePath,
                'disease_detected' => $disease,
                'water_quality_status' => $water,
                'solution' => $solution,
                'raw_ai_response' => $aiTextResponse, 
            ]);

                        \App\Models\Notification::create([
                'user_id' => $request->user() ? $request->user()->id : null,
                'title' => '🤖 Analisa AI AQUARA Selesai!',
                'message' => 'Pakar AI telah mendeteksi kondisi tambak/ikan Anda. Cek sekarang!',
                'type' => 'ai'
            ]);

            try {
                $firebase = (new \Kreait\Firebase\Factory)->withServiceAccount(storage_path('app/firebase-auth.json'));
                $messaging = $firebase->createMessaging();

                $message = \Kreait\Firebase\Messaging\CloudMessage::new()
                    ->withNotification(\Kreait\Firebase\Messaging\Notification::create(
                        '🤖 Analisa AI AQUARA Selesai!',
                        "Pakar AI telah mendeteksi kondisi tambak/ikan Anda. Cek hasil dan solusinya sekarang!"
                    ))
                    ->withData(['type' => 'ai'])->toTopic('all_users');

                $messaging->send($message);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('FCM Error AI: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'message' => 'Analisa AI berhasil!',
                'data' => $consultation
            ]);

        } else {
            $statusCode = $response->status();
            
            if ($statusCode == 429) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sistem AQUARA sedang sibuk melayani banyak petani. Antrean sistem sedang penuh, silakan coba lagi dalam 1 menit ya!'
                ], 429);
            }

            return response()->json([
                'success' => false,
                'message' => 'Maaf, Sistem AQUARA sedang mengalami gangguan jaringan. Silakan periksa koneksi internet Anda dan coba lagi. (Kode: ' . $statusCode . ')'
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AsistenChat;
use App\Support\AsistenReply;
use App\Support\GombyangResponses;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AsistenChatController extends Controller
{
    public function store(Request $request, AsistenReply $asisten, GombyangResponses $ai): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'max:2000'],
            'user_id' => ['missing'],
            'response' => ['missing'],
            'reply' => ['missing'],
            'is_mock' => ['missing'],
            'jawaban' => ['missing'],
            'pertanyaan' => ['missing'],
        ]);

        // Decode entities first, then strip all HTML and non-printing control characters.
        $message = html_entity_decode($data['message'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $message = strip_tags($message);
        $message = preg_replace('/[\p{Cc}\p{Cf}]+/u', ' ', $message) ?? '';
        $message = trim(preg_replace('/\s+/u', ' ', $message) ?? '');
        Validator::make(['message' => $message], [
            'message' => ['required', 'string', 'max:2000'],
        ])->validate();

        $chat = new AsistenChat;
        $chat->user()->associate($request->user());
        $chat->pertanyaan = $message;
        $provider = config('services.gombyang_provider', 'openai');
        $key = in_array($provider, ['groq', 'openai'], true) ? trim((string) config("services.$provider.key")) : '';
        $chat->is_mock = $key === '' && $provider === 'openai' && !app()->environment('production');
        $answer = $chat->is_mock ? $asisten->reply($message) : ($key === '' ? null : $ai->answer($message, $key));
        if ($answer === null) {
            return response()->json(['success' => false, 'message' => 'Gombyang belum dapat menjawab.'], 503)
                ->header('Cache-Control', 'private, no-store');
        }
        $chat->jawaban = $answer;
        $chat->save();

        return $this->respond('Pesan berhasil diproses', [
            'id' => $chat->id,
            'message' => $chat->pertanyaan,
            'reply' => $chat->jawaban,
            'is_mock' => $chat->is_mock,
            'created_at' => $chat->created_at,
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $chats = AsistenChat::ownedBy($request->user())->latest()->orderByDesc('id')->paginate(20);

        return $this->respond('Riwayat chat berhasil diambil', $chats);
    }

    public function show(Request $request, string $id): JsonResponse
    {
        // No role bypass: unrelated and orphaned chats are always inaccessible.
        $chat = AsistenChat::ownedBy($request->user())->findOrFail($id);

        return $this->respond('Detail chat berhasil diambil', $chat);
    }

    private function respond(string $message, mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status)->header('Cache-Control', 'private, no-store');
    }
}
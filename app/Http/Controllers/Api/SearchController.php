<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommunityPost;
use App\Models\Pasar;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $input = $request->query();
        if (isset($input['q']) && is_string($input['q'])) {
            $input['q'] = preg_replace('/^[\s\p{Z}]+|[\s\p{Z}]+$/u', '', $input['q']);
        }
        $data = Validator::make($input, [
            'q' => 'required|string|min:1|max:100',
            'per_page' => 'sometimes|integer|min:1|max:50',
            'pasar_page' => 'sometimes|integer|min:1|max:1000',
            'forum_page' => 'sometimes|integer|min:1|max:1000',
            'berita_page' => 'sometimes|integer|min:1|max:1000',
        ])->validate();

        // Fixed identifiers only; input is a bound value. Escape LIKE metacharacters
        // explicitly, without depending on a database's backslash escape mode.
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($data['q'], 'UTF-8')).'%';
        $groups = [
            'pasar' => [Pasar::class, 'nama_ikan', 'deskripsi', 'foto'],
            'forum' => [CommunityPost::class, 'content', 'content', 'image'],
            'berita' => [Post::class, 'title', 'content', 'image'],
        ];
        $results = [];
        foreach ($groups as $type => [$model, $field, $summary, $image]) {
            $pageName = $type.'_page';
            $page = $model::query()
                ->select(array_unique(['id', $field, $summary, $image]))
                ->whereRaw("LOWER({$field}) LIKE ? ESCAPE '!'", [$pattern])
                ->orderByDesc('id')
                ->paginate((int) ($data['per_page'] ?? 10), ['*'], $pageName, (int) ($data[$pageName] ?? 1));

            $results[$type] = [
                'data' => $page->getCollection()->map(fn ($item) => [
                    'type' => $type,
                    'id' => (int) $item->id,
                    'title' => $type === 'forum' ? null : $item->{$field},
                    'summary' => $item->{$summary} === null ? null : Str::limit(strip_tags($item->{$summary}), 200),
                    'thumbnail' => $item->{$image},
                ])->all(),
                'meta' => [
                    'current_page' => $page->currentPage(),
                    'per_page' => $page->perPage(),
                    'total' => $page->total(),
                    'last_page' => $page->lastPage(),
                    'has_more_pages' => $page->hasMorePages(),
                ],
            ];
        }

        return response()->json(['success' => true, 'data' => $results])
            ->header('Cache-Control', 'private, no-store');
    }
}

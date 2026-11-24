<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'name' => $this->name,
            'foto_profil' => $this->foto_profil,
            'fokus_budidaya' => $this->fokus_budidaya,
        ];
    }

    public static function author($model, Request $request): ?array
    {
        // Never lazy-load or infer identity from a legacy name/string user_id.
        $user = $model->relationLoaded('user') ? $model->getRelation('user') : null;
        if (! $user || (string) $model->user_id !== (string) $user->id) {
            return null;
        }

        // Nested identities retain their existing allowlist, without business details.
        return [
            'id' => (int) $user->id,
            'name' => $user->name,
            'foto_profil' => $user->foto_profil,
        ];
    }
}

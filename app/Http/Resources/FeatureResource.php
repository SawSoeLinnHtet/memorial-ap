<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FeatureResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'image_url' => $this->image_url
                ? route('storage.public', ['path' => ltrim($this->image_url, '/')])
                : null,
            'memorial_text' => $this->memorial_text,
            'memorial_date' => $this->memorial_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

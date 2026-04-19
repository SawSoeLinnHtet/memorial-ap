<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CollectionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'memorial_date' => $this->date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'features' => FeatureResource::collection($this->features),
        ];
    }
}

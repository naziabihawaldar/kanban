<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BoardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'board_assignees' => UserResource::collection($this->whenLoaded('assignees')),
            'columns' => ColumnResource::collection($this->whenLoaded('columns')),
            'imports' => $this->whenLoaded('imports'),
        ];

        //
    }
}

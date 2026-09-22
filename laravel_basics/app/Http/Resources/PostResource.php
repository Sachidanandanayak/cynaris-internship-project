<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Week 4 Day 2 Requirement:
     * - Transform Post model attributes into a consistent JSON response.
     * - Only expose necessary fields (id, title, slug, body, comments_count, timestamps).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'body'           => $this->body,
            'comments_count' => $this->when(isset($this->comments_count), fn () => (int) $this->comments_count),
            'comments'       => $this->whenLoaded('comments'),
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}

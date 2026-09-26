<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'title'                => $this->title,
            'company'              => $this->company,
            'location'             => $this->location,
            'employment_type'      => $this->employment_type,
            'description'          => $this->description,
            'requirements'         => $this->requirements,
            'salary_range'         => $this->salary_range,
            'application_deadline' => $this->application_deadline?->toDateString(),
            'status'               => $this->status,
            'user_id'              => $this->user_id,
            'poster'               => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
            'applications_count'   => $this->when(isset($this->applications_count), (int) $this->applications_count),
            'created_at'           => $this->created_at?->toIso8601String(),
            'updated_at'           => $this->updated_at?->toIso8601String(),
        ];
    }
}

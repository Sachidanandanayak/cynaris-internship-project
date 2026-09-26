<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'job_id'       => $this->job_id,
            'user_id'      => $this->user_id,
            'cover_letter' => $this->cover_letter,
            'resume_url'   => $this->resume_url,
            'status'       => $this->status,
            'job'          => $this->whenLoaded('job', fn () => [
                'id'              => $this->job->id,
                'title'           => $this->job->title,
                'company'         => $this->job->company,
                'location'        => $this->job->location,
                'employment_type' => $this->job->employment_type,
                'salary_range'    => $this->job->salary_range,
                'status'          => $this->job->status,
            ]),
            'candidate'    => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}

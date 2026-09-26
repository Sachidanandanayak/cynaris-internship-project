<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class UpdateJobApiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'                => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'company'              => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'location'             => ['sometimes', 'required', 'string', 'min:2', 'max:255'],
            'employment_type'      => ['sometimes', 'required', 'string', 'in:Full-time,Part-time,Contract,Remote,Internship'],
            'description'          => ['sometimes', 'required', 'string', 'min:10'],
            'requirements'         => ['sometimes', 'required', 'string', 'min:10'],
            'salary_range'         => ['nullable', 'string', 'max:100'],
            'application_deadline' => ['nullable', 'date'],
            'status'               => ['sometimes', 'required', 'string', 'in:active,closed,draft'],
        ];
    }
}

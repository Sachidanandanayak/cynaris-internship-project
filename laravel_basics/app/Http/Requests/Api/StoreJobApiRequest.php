<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreJobApiRequest extends FormRequest
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
            'title'                => ['required', 'string', 'min:3', 'max:255'],
            'company'              => ['required', 'string', 'min:2', 'max:255'],
            'location'             => ['required', 'string', 'min:2', 'max:255'],
            'employment_type'      => ['required', 'string', 'in:Full-time,Part-time,Contract,Remote,Internship'],
            'description'          => ['required', 'string', 'min:10'],
            'requirements'         => ['required', 'string', 'min:10'],
            'salary_range'         => ['nullable', 'string', 'max:100'],
            'application_deadline' => ['nullable', 'date'],
            'status'               => ['nullable', 'string', 'in:active,closed,draft'],
        ];
    }
}

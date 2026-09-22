<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePostApiRequest extends FormRequest
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
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'slug'  => ['nullable', 'string', 'min:3', 'max:255', 'alpha_dash', 'unique:posts,slug'],
            'body'  => ['required', 'string', 'min:10'],
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The post title is required.',
            'title.min'      => 'The post title must be at least :min characters.',
            'title.max'      => 'The post title cannot exceed :max characters.',
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
            'slug.unique'    => 'This URL slug is already taken.',
            'body.required'  => 'The post body is required.',
            'body.min'       => 'The post body must be at least :min characters.',
        ];
    }
}

<?php

namespace App\Http\Requests\Api;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePostApiRequest extends FormRequest
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
        $routeParam = $this->route('post');
        $postId = $routeParam instanceof Post ? $routeParam->id : $routeParam;

        return [
            'title' => ['sometimes', 'required', 'string', 'min:3', 'max:255'],
            'slug'  => [
                'sometimes',
                'nullable',
                'string',
                'min:3',
                'max:255',
                'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($postId),
            ],
            'body'  => ['sometimes', 'required', 'string', 'min:10'],
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
            'title.required' => 'The post title cannot be empty.',
            'title.min'      => 'The post title must be at least :min characters.',
            'title.max'      => 'The post title cannot exceed :max characters.',
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
            'slug.unique'    => 'This URL slug is already taken.',
            'body.required'  => 'The post body cannot be empty.',
            'body.min'       => 'The post body must be at least :min characters.',
        ];
    }
}

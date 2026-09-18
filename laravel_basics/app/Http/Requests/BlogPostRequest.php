<?php

namespace App\Http\Requests;

use App\Models\Post;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogPostRequest extends FormRequest
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
        // Resolve post ID from route parameter 'blog' (or 'post' if aliased)
        $routeParam = $this->route('blog') ?? $this->route('post');
        $postId = $routeParam instanceof Post ? $routeParam->id : $routeParam;

        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'slug'  => [
                'required',
                'string',
                'min:3',
                'max:255',
                'alpha_dash',
                Rule::unique('posts', 'slug')->ignore($postId),
            ],
            'body'  => ['required', 'string', 'min:10'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required'  => 'The post title is required.',
            'title.min'       => 'The post title must be at least :min characters.',
            'title.max'       => 'The post title cannot exceed :max characters.',
            'slug.required'   => 'The URL slug is required.',
            'slug.min'        => 'The slug must be at least :min characters.',
            'slug.max'        => 'The slug cannot exceed :max characters.',
            'slug.alpha_dash' => 'The slug may only contain letters, numbers, dashes, and underscores.',
            'slug.unique'     => 'This URL slug is already taken. Please choose a unique slug.',
            'body.required'   => 'The post body content is required.',
            'body.min'        => 'The post body must be at least :min characters.',
        ];
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StorePostApiRequest;
use App\Http\Requests\Api\UpdatePostApiRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PostApiController extends Controller
{
    /**
     * Display a listing of posts.
     *
     * GET /api/posts
     * Status: 200 OK
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = $request->integer('per_page', 10);
        $posts = Post::withCount('comments')
            ->latest()
            ->paginate($perPage);

        return PostResource::collection($posts);
    }

    /**
     * Display the specified post.
     *
     * GET /api/posts/{post}
     * Status: 200 OK (or 404 Not Found)
     */
    public function show(Post $post): PostResource
    {
        $post->loadCount('comments');
        $post->load('comments');

        return new PostResource($post);
    }

    /**
     * Store a newly created post in storage.
     *
     * POST /api/posts (Sanctum protected)
     * Status: 201 Created (or 422 Unprocessable Entity, 401 Unauthorized)
     */
    public function store(StorePostApiRequest $request): JsonResponse
    {
        $post = Post::create($request->validated());
        $post->loadCount('comments');

        return (new PostResource($post))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Update the specified post in storage.
     *
     * PUT/PATCH /api/posts/{post} (Sanctum protected)
     * Status: 200 OK (or 422 Unprocessable Entity, 401 Unauthorized, 404 Not Found)
     */
    public function update(UpdatePostApiRequest $request, Post $post): JsonResponse
    {
        $post->update($request->validated());
        $post->loadCount('comments');

        return (new PostResource($post))
            ->response()
            ->setStatusCode(200);
    }

    /**
     * Remove the specified post from storage.
     *
     * DELETE /api/posts/{post} (Sanctum protected)
     * Status: 200 OK (or 401 Unauthorized, 404 Not Found)
     */
    public function destroy(Post $post): JsonResponse
    {
        $post->delete();

        return response()->json([
            'message' => 'Post deleted successfully.',
        ], 200);
    }
}

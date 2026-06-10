<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;
use App\Http\Resources\PostCollection;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Services\PostService;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class PostController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    protected PostService $postService;

    public function __construct(PostService $postService) {
        $this->postService = $postService;
    }

    public function index(){
        $post = $this->postService->getAllPaginated();
        return new PostCollection($post);
    }

    public function store(StorePostRequest $request){
        $post = $this->postService->createPost($request->validated(), auth()->id());
        return $this->success(new PostResource($post), 'Post created successfully', 201);
    }

    public function show(Post $post){
        return $this->success(new PostResource($post->load(['user', 'comments.user'])));
    }

    public function update(UpdatePostRequest $updatePostRequest, Post $post){
        $this->authorize('update', $post);
        $updatedPost = $this->postService->updatePost($post, $updatePostRequest->validated());
        return $this->success(new PostResource($updatedPost), 'POst updated successfully');
    }

    public function destroy(Post $post) {
        $this->authorize('delete', $post);
        $this->postService->deletePost($post);
        return $this->success(null, 'Post deleted successfully');
    }
}

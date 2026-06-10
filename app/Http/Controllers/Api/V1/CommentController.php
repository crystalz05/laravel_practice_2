<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Comment\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Services\CommentService;
use App\Traits\ApiResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    use ApiResponse, AuthorizesRequests;

    protected CommentService $commentService;

    public function __construct(CommentService $commentService) {
        $this->commentService = $commentService;
    }

    public function index(Post $post){
        $comments = $this->commentService->getPostCommentsPaginated($post);
        return $this->success(
            CommentResource::collection($comments)->response()->getData(),
            'Comments fetched'
        );
    }

    public function store(StoreCommentRequest $storeCommentRequest, Post $post){
        $comment = $this->commentService->createComment($post, $storeCommentRequest->validated(), auth()->id());
        return $this->success(new CommentResource($comment), 'Comment created successfully', 201);
    }

    public function destroy(Comment $comment){
        $this->authorize('delete', $comment);
        $this->commentService->deleteComment($comment);
        return $this->success(null, 'Comment deleted successfully');
    }
    //
}


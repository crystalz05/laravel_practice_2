<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService {
    /**
     * Fetch all comments on a specific post, paginated and public.
     */
    public function getPostCommentsPaginated(Post $post, int $perPage = 15): LengthAwarePaginator {
        return $post->comments()
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a comment on a target post.
     */
    public function createComment(Post $post, array $data, int $userId): Comment {
        return Comment::create([
            'body'    => $data['body'],
            'post_id' => $post->id,
            'user_id' => $userId,
        ]);
    }

    /**
     * Perform a soft-delete on a target comment.
     */
    public function deleteComment(Comment $comment): bool {
        return $comment->delete();
    }
}

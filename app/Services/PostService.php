<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostService {
    public function getAllPaginated(int $perPage = 15): LengthAwarePaginator {
        return Post::with(['user', 'comments.user'])->latest()->paginate($perPage);
    }

    public function createPost(array $data, int $userId): Post {
        return Post::create(array_merge($data, ['user_id' => $userId]));
    }

    public function updatePost(Post $post, array $data): Post {
        $post->update($data);
        return $post;
    }

    public function deletePost(Post $post): bool {
        return $post->delete();
    }
}

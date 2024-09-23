<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\Product;
use InvalidArgumentException;

class CommentService
{
    public function getAllComments()
    {
        $comments = Comment::paginate();

        if (!$comments) {
            throw new InvalidArgumentException('There Is No Comments Available');
        }

        return $comments;
    }

    public function getCommentsByProductId(int $product_id)
    {
        $comments = Product::findOrFail($product_id)
        ->with('comments')->get();

        if (!$comments) {
            throw new InvalidArgumentException('There Is No Comments Available');
        }

        return $comments;
    }

    public function getComment(int $comment_id) : Comment
    {
        $comment = Comment::findOrFail($comment_id);

        return $comment;
    }

    public function createComment(array $data, int $user_id, int $sku_id): Comment
    {
        $comment = Comment::create([
            'user_id' => $user_id,
            'sku_id' => $sku_id,
            'comment' => $data['comment'],
        ]);

        if(!$comment){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $comment;
    }

    public function updateComment(array $data, int $comment_id): Comment
    {
        $comment = Comment::findOrFail($comment_id);
        if(!$comment){
            throw new InvalidArgumentException('There Is No Comments Available');
        }
        $comment->update([
            'comment' => $data['comment'],
        ]);

        return $comment;
    }

    public function show(int $comment_id): Comment
    {
        $comment = Comment::findOrFail($comment_id);

        return $comment;
    }

    public function delete(int $comment_id) : void
    {
        $comment = Comment::findOrFail($comment_id);

        $comment->delete();
    }

    public function forceDelete(int $comment_id) : void
    {
        $comment = Comment::findOrFail($comment_id);

        $comment->forceDelete();
    }
}

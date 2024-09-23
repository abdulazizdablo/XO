<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Order;
use App\Models\User;
use InvalidArgumentException;

class FeedbackService
{
    public function getAllFeedbacks()
    {
        $feedbacks = Feedback::paginate(10);

        if (!$feedbacks) {
            throw new InvalidArgumentException('There Is No Feedbacks Available');
        }

        return $feedbacks;
    }

    public function getFeedback(int $feedback_id) : Feedback
    {
        $feedback = Feedback::findOrFail($feedback_id);

      /*  if (!$feedback) {
            throw new InvalidArgumentException('Feedback not found');
        }*/

        return $feedback;
    }

    public function createFeedback(array $data, $user_id, $order_id): Feedback
    {
        // $user = User::find($user_id)->get();
        // $order = Order::find($order_id)->get();
        $feedback = Feedback::create([
            'user_id' => $user_id,
            'order_id' => $order_id,
            'content' => $data['content'],
            'rate' => $data['rate'],
            'type' => $data['type'],
            'status' => $data['status'],
        ]);
        if(!$feedback){
            throw new InvalidArgumentException('Something Wrong Happend');
        }

        return $feedback;
    }

    public function updateFeedback(array $data, int $feedback_id, int $user_id,  int $order_id): Feedback
    {
        $feedback = Feedback::findOrFail($feedback_id);
    
        $feedback->update([
            'name' => $data['name'],
            'type' => $data['type'],
            'valid' => $data['valid'],
            'description' => $data['description'],
            'expired_at' => $data['expired_at'],
        ]);

        return $feedback;
    }

    public function show(int $feedback_id): Feedback
    {
        $feedback = Feedback::findOrFail($feedback_id);

        return $feedback;
    }

    public function delete(int $feedback_id) : void
    {
        $feedback = Feedback::findOrFail($feedback_id);

        $feedback->delete();
    }

    public function forceDelete(int $feedback_id) : void
    {
        $feedback = Feedback::findOrFail($feedback_id);

        $feedback->forceDelete();
    }
}

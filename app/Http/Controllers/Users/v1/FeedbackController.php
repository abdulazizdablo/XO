<?php

namespace App\Http\Controllers\Users\v1;

use App\Http\Controllers\Controller;
use App\Models\Feedback;
use App\Services\FeedbackService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class FeedbackController extends Controller
{


    public function __construct(
        protected   FeedbackService $feedbackService
    ) {
    }

    /**
     *
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            $user_id  = $user->id;
            $order_id = request('order_id');
            $validate = Validator::make(
                $request->all(),
                [
                    'feedback.content' => 'required|string|max:255',
                    'feedback.rate' => 'required|numeric|between:1,5',
                    'feedback.type' => 'required|string|max:255',
                    'feedback.status' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                  422
                );
            }
            $feedbackService = $this->feedbackService->createFeedback($validate->validated()['feedback'], $user_id, $order_id);

            return response()->success(
                [
                    'message' => 'Feedback Is Created',
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_BAD_REQUEST
            );
        }
    }
}

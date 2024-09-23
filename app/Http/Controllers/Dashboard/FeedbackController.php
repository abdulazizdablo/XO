<?php

namespace App\Http\Controllers\Dashboard;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $feedbacks = $this->feedbackService->getAllFeedbacks();

        return response()->success([
            'coupns' => $feedbacks
        ], Response::HTTP_OK);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
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
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();
            $feedback_data = $validated_data['feedback'];

            $feedback = $this->feedbackService->createFeedback($feedback_data, $user_id, $order_id);

            return response()->success(
                [
                    'message' => 'Feedback Is Created',
                    'data' => $feedback,
                ],
                Response::HTTP_CREATED
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $feedback_id = request('feedback_id');
            $feedback = $this->feedbackService->getFeedback($feedback_id);

            return response()->success(
                [
                    'feedback' => $feedback
                ],
                Response::HTTP_FOUND
            );
        } catch (InvalidArgumentException $e) {
            return response()->error(
                $e->getMessage(),
                Response::HTTP_NOT_FOUND
            );
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function edit(Feedback $feedback)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $user = auth('sanctum')->user();
            $user_id  = $user->id;
            $order_id = request('order_id');
            $feedback_id = request('feedback_id');

            $validate = Validator::make(
                $request->all(),
                [
                    'feedback.content' => 'sometimes|string|max:255',
                    'feedback.rate' => 'sometimes|numeric|between:1,5',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $validated_data = $validate->validated();
            $feedback_data = $validated_data['feedback'];

            $feedback = $this->feedbackService->updateFeedback($feedback_data, $feedback_id, $user_id, $order_id);

            return response()->success(
                [
                    'message' => 'Feedback Updated Successfully',
                    'data' => $feedback,
                ],
                Response::HTTP_OK
                // OR HTTP_NO_CONTENT
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $feedback_id = request('feedback_id');
            $feedback = $this->feedbackService->delete($feedback_id);

            return response()->success(
                [
                    'message' => 'Feedback Deleted Successfully',
                    'data' => $feedback,
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Feedback  $feedback
     * @return \Illuminate\Http\Response
     */
    public function forceDelete()
    {
        try {
            $feedback_id = request('feedback_id');
            $feedback = $this->feedbackService->forceDelete($feedback_id);

            return response()->success(
                [
                    'message' => 'Feedback Deleted Successfully',
                    'data' => $feedback,
                ],
                Response::HTTP_OK
            );
        } catch (\Throwable $th) {
            return response()->error(
                $th->getMessage(),
                Response::HTTP_OK
            );
        }
    }
}

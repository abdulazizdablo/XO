<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{


    public function __construct(
        protected   CommentService $commentService
    ) {
        
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $comments = $this->commentService->getAllComments();

        return response()->success([
            'Comments' => $comments
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
            $sku_id = request('sku_id');
            $user = auth('sanctum')->user();
            
            $validate = Validator::make(
                $request->all(),
                [
                    'comment.comment' => 'required|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
422                );
            }

            $validated_data = $validate->validated();
            $comment_data = $validated_data['comment'];

            $comment = $this->commentService->createComment($comment_data,$user->id, $sku_id);

            return response()->success(
                [
                    'message' => 'Comment Is Created',
                    'data' => $comment
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
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        try {
            $comment_id = request('comment_id');
            $comment = $this->commentService->getComment($comment_id);

            return response()->success(
                [
                    'comment' => $comment
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
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function edit(Comment $comment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $sku_id = request('sku_id');
            $user = auth('sanctum')->user();
            $user_id  = $user->id;
            $comment_id = request('comment_id');

            $validate = Validator::make(
                $request->all(),
                [
                    'comment.comment' => 'sometimes|string|max:255',
                ]
            );

            if ($validate->fails()) {
                return response()->error(
                    $validate->errors(),
                    Response::HTTP_OK
                );
            }

            $validated_data = $validate->validated();
            $comment_data = $validated_data['comment'];

            $comment = $this->commentService->updateComment($comment_data, $comment_id, $user_id, $sku_id);

            return response()->success(
                [
                    'message' => 'Comment Updated successfully',
                    'data' => $comment
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
     * @param  \App\Models\Comment  $comment
     * @return \Illuminate\Http\Response
     */
    public function destroy()
    {
        try {
            $comment_id = request('comment_id');
            $comment = $this->commentService->delete($comment_id);

            return response()->success(
                [
                    'message' => 'Comment deleted successfully',
                    'data' => $comment
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

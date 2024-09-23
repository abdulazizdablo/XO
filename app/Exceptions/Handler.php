<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Throwable;

class Handler extends ExceptionHandler
{
    
    
    
    
    
      public function render($request, Throwable $exception)
    {
       /* if ($exception instanceof ModelNotFoundException) {
            return response()->error(class_basename($exception->getModel()) . ' Not Found', 404);
        } else if*/
        
        
          if ($exception instanceof ModelNotFoundException) {
            return response()->error(class_basename($exception->getModel()) . ' Not Found', 404);
          }
        if($exception instanceof OutOfStockException) {
            return response()->json([
            'error' => true,
           // 'message' => $exception->getMessage(),
            'product_quantity_issues' => $exception->getData(), // Retrieve the array from the exception
        ],   400); // Set t
        }

        return parent::render($request, $exception);
    }
    
    
    
    
    
    
    
    
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (ModelNotFoundException $exception, Request $request) {
            return response()->error(class_basename($exception->getModel()).' Not Found', 404);
        });
    }
}

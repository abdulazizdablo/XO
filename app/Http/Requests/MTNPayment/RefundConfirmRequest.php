<?php

namespace App\Http\Requests\MTNPayment;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

class CreateInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [

          
            'Amount' => 'required|integer|not_in:0',
           // 'transactionID' => 'required|string',
           // 'token' => 'required|string'
        ];
    }
	
	 /* protected function failedValidation(Validator $validator)
    {
        $general_errors = $validator->errors();

$mobile_errors =[];
		  
		 
		  foreach ($general_errors as $key => $mobile_error){
			  foreach($mobile_error as $key => $message){
			  
			  
			  $mobile_errors[key($mobile_error)] = $message;
			  
			  }
		
		  
		  $mobile_errors[$key] = $mobile_error;
		  
		  }
		  
		  $errors = ['errors' => $general_errors ,'mobile_errors' => $mobile_errors ];
		  

        throw new HttpResponseException(
            response()->json(['errors' => $errors], JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
        );
    }*/
	
}

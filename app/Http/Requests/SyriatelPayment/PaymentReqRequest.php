<?php

namespace App\Http\Requests\SyriatelPayment;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use App\Rules\OrderAmount;

class PaymentReqRequest extends FormRequest
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
     $rules = [
            'customerMSISDN' => 'required|string|digits:10',
            'amount' => ['sometimes','string','not_in:0'],
            'order_id' => ['required_without:gift_id,gift_code','exists:orders,id'],
            'gift_id' => [
                'required_with:gift_code',
                'exists:coupons,id',
            ],
            'gift_code' => [
                'required_with:gift_id',
                'exists:coupons,code',
                'string',
            ],
            // 'transactionID' => 'required|string',
            // 'token' => 'required|string'
        ];
	
        if ($this->has('order_id')) {
		
			//$rules['order_id'] = ['required_without:gift_id,gift_code','exists:orders,id'];
            $rules['amount'] = [
				'bail',
                'required',
                'integer',
                'not_in:0',
                new OrderAmount($this->input('order_id'),$this->input('amount')),
            ];
        } else {
            $rules['amount'] = [
                'required',
                'integer',
                'not_in:0',
            ];
        }
        return $rules;

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

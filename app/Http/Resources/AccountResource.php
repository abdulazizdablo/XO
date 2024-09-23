<?php

namespace App\Http\Resources;

use App\Models\Employee;
use Illuminate\Http\Resources\Json\JsonResource;

class AccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
      return [
          'email' => $this->email,
          'password' => $this->password,
          'role' => $this->roles->first(),
          'assigned' => Employee::where('account_id',$this->id)->get()

      ];
    }
}

<?php

namespace App\Http\Controllers\Users\v1;

use App\Models\User;
use App\Models\Address;
use App\Http\Controllers\Controller;
use App\Http\Resources\AddressCollection;
use App\Http\Resources\AddressResource;
use App\Services\AddressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Response;
use App\Models\City;
use App\Models\Contact;


class ContactController extends Controller

{


    public function store(Request $request){

$contact = Contact::create($request->all());


return response()->success($contact,200);

    }





}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\ContactMail;

class ContactController extends Controller
{
    public function sendContact(Request $request){
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255',
            'message' => 'required|string',
        ]);

        try {
            // إرسال البريد الإلكتروني إلى عنوان الشركة
//           Mail::to(env('MAIL_TO_ADDRESS'))->send(new ContactMail($data));
            Mail::to("ahmadshahla410@gmail.com")->send(new ContactMail($data));
            return response()->json([
                'message' => 'تم إرسال الرسالة بنجاح'
            ], 200);
        } catch (\Exception $e) {
            // في حالة حدوث خطأ أثناء الإرسال
            return response()->json([
                'message' => 'فشل في إرسال الرسالة',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}

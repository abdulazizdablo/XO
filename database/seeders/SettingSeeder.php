<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Setting;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $type_of_problems_userComplaints = Setting::create([
            "key" => 'type_of_problems',
            "value" =>json_encode(['Product specifications do not match',
            'Delivery problem',
            'Damaged product',
            'Technical problem',
            'Others'])
        ]);
        //
        $loginNotification = Setting::create([
            "key" => 'loginNotification',
            "value" =>
            '{"title":"Welcome in XO","body": "We wish you a pleasant trip in XO"}'

        ]);

        $BanUserNotification = Setting::create([
            "key" => 'BanUserNotification',
            "value" =>
            '{"title":"Your account has been blocked","body": "Your account has been blocked. Please contact the admin if you wish to recover the account"}'

        ]);

        $links = Setting::create([

            "key" => 'links',
            "value" =>
            '{"phone":"(+963 987 782 466)","helpdesk":"(+963 987 782 466)","facebook":"","instagram":"","whatsapp":"(+963 945 673 389)","email":"xo.customer@xogroup.com" , "google_play":"","app_store":"#"}'


        ]);

        $fees = Setting::create([
            "key" => "fees",
            "value" => '{"shipping_fee":20 ,"free_shipping":200}'
        ]);

        $return_policy = Setting::create([
            "key" => "return_policy",
            "value" => '{"title":"You can return the order within" ,"days":20}'
        ]);

        $photos = Setting::create([
            "key" => "photos",
            "value" => '{
                "photo1": {
                    "link": "///",
                    "OnClick": "0"
                },
                "photo2": {
                    "link": "///",
                    "OnClick": "0"
                },
                "photo3": {
                    "link": "///",
                    "OnClick": "1"
                }
            }',
        ]);

        $aboutUs = Setting::create([
            "key" => "aboutUs",
            "value" => '{
                "aboutUs": {
                    "en": "This text is an example of text that can be replaced in the same space. This text was generated from the Arabic text generator, where you can generate such text or many other texts in addition to increasing the number of letters that the application generates. If you need a larger number of paragraphs, the Arabic text generator allows you to increase the number of paragraphs as you want. The text will not appear divided and does not contain linguistic errors. The Arabic text generator is useful for website designers in particular, as the client often needs to see a real picture of the website design. Hence, it is necessary for the designer to create texts.",
                    "ar": "هذا النص هو مثال لنص يمكن أن يستبدل في نفس المساحة، لقد تم توليد هذا النص من مولد النص العربي، حيث يمكنك أن تولد مثل هذا النص أو العديد من النصوص الأخرى بالإضافة إلى زيادة عدد الحروف التي يولدها التطبيق. إذا كنت تحتاج إلى عدد أكبر من الفقرات، يتيح لك مولد النص العربي زيادة عدد الفقرات كما تريد. النص لن يبدو مقسمًا ولا يحوي أخطاء لغوية. مولد النص العربي مفيد لمصممي المواقع بشكل خاص، حيث يحتاج العميل في كثير من الأحيان إلى رؤية صورة حقيقية لتصميم الموقع. ومن هنا يتعين على المصمم إنشاء نصوص."
                }
            }',
        ]);
        $Advertisement_tape = Setting::create([
            "key" => "Advertisement_tape",
            "value" => '{"sentence1":"عروض حصريا على جميع البناطيل والشورتات لهذا الصيف  اغتنم الفرصة",
                "sentence2":"عروض حصريا على جميع البناطيل والشورتات لهذا الصيف  اغتنم الفرصة" ,
                "sentence3":"عروض حصريا على جميع البناطيل والشورتات لهذا الصيف  اغتنم الفرصة"}'
        ]);

        $filters = Setting::create([
            "key" => "filter",
            "value" => '{
                "price": {
                    "max": "250",
                    "min": "1000000"
                }
            }',
        ]);
        $frequent_question = Setting::create([
            "key" => "frequent_question",
            "value" => '{
                "question1": {
                    "question": {
                        "en": "hi?",
                        "ar": "هاي ؟"
                    },
                    "answer": {
                        "en": "hi?",
                        "ar": "هاي ؟"
                    }
                },
                "question2": {
                    "question": {
                        "en": "hi?",
                        "ar": "هاي ؟"
                    },
                    "answer": {
                        "en": "hi?",
                        "ar": "هاي ؟"
                    }
                }
            }'
        ]);

        $privacy_policy = Setting::create([
            "key" => "privacy_policy",
            "value" => '{"privacy_policy":{"en":"This text is an example of text that can be replaced in the same space. This text was generated from the Arabic text generator, where you can generate such text or many other texts in addition to increasing the number of letters that the application generates. If you need a larger number of Paragraphs The Arabic text generator allows you to increase the number of paragraphs as you want. The text will not appear divided and does not contain linguistic errors. The Arabic text generator is useful for website designers in particular, as the client often needs to see a real picture of the website design. Hence, it is necessary to The designer has to create texts" , "ar":"هذا النص هو مثال لنص يمكن أن يستبدل في نفس المساحة، لقد تم توليد هذا النص من مولد النص العربى، حيث يمكنك أن تولد مثل هذا النص أو العديد من النصوص الأخرى إضافة إلى زيادة عدد الحروف التى يولدها التطبيق.إذا كنت تحتاج إلى عدد أكبر من الفقرات يتيح لك مولد النص العربى زيادة عدد الفقرات كما تريد، النص لن يبدو مقسما ولا يحوي أخطاء لغوية، مولد النص العربى مفيد لمصممي المواقع على وجه الخصوص، حيث يحتاج العميل فى كثير من الأحيان أن يطلع على صورة حقيقية لتصميم الموقع.ومن هنا وجب على المصمم أن يضع نصوصا"}}'

        ]);

        $Complaints = Setting::create([
            "key" => "Complaints",
            "value" => '{"Complaints":{"en":"This text is an example of text that can be replaced in the same space. This text was generated from the Arabic text generator, where you can generate such text or many other texts in addition to increasing the number of letters that the application generates. If you need a larger number of Paragraphs The Arabic text generator allows you to increase the number of paragraphs as you want. The text will not appear divided and does not contain linguistic errors. The Arabic text generator is useful for website designers in particular, as the client often needs to see a real picture of the website design. Hence, it is necessary to The designer has to create texts" , "ar":"هذا النص هو مثال لنص يمكن أن يستبدل في نفس المساحة، لقد تم توليد هذا النص من مولد النص العربى، حيث يمكنك أن تولد مثل هذا النص أو العديد من النصوص الأخرى إضافة إلى زيادة عدد الحروف التى يولدها التطبيق.إذا كنت تحتاج إلى عدد أكبر من الفقرات يتيح لك مولد النص العربى زيادة عدد الفقرات كما تريد، النص لن يبدو مقسما ولا يحوي أخطاء لغوية، مولد النص العربى مفيد لمصممي المواقع على وجه الخصوص، حيث يحتاج العميل فى كثير من الأحيان أن يطلع على صورة حقيقية لتصميم الموقع.ومن هنا وجب على المصمم أن يضع نصوصا"}}'

        ]);

        $terms = Setting::create([
            "key" => "terms",
            "value" => '{"terms":{"en":"This text is an example of text that can be replaced in the same space. This text was generated from the Arabic text generator, where you can generate such text or many other texts in addition to increasing the number of letters that the application generates. If you need a larger number of Paragraphs The Arabic text generator allows you to increase the number of paragraphs as you want. The text will not appear divided and does not contain linguistic errors. The Arabic text generator is useful for website designers in particular, as the client often needs to see a real picture of the website design. Hence, it is necessary to The designer has to create texts" , "ar":"هذا النص هو مثال لنص يمكن أن يستبدل في نفس المساحة، لقد تم توليد هذا النص من مولد النص العربى، حيث يمكنك أن تولد مثل هذا النص أو العديد من النصوص الأخرى إضافة إلى زيادة عدد الحروف التى يولدها التطبيق.إذا كنت تحتاج إلى عدد أكبر من الفقرات يتيح لك مولد النص العربى زيادة عدد الفقرات كما تريد، النص لن يبدو مقسما ولا يحوي أخطاء لغوية، مولد النص العربى مفيد لمصممي المواقع على وجه الخصوص، حيث يحتاج العميل فى كثير من الأحيان أن يطلع على صورة حقيقية لتصميم الموقع.ومن هنا وجب على المصمم أن يضع نصوصا"}}'

        ]);

        // $terms = Setting::create([
        //     "key" => "gift_card",
        //     "value" => '{[200 , 300 , 400 , 500]}'

        // ]);


    }
}

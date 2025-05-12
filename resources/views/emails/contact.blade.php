<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>رسالة اتصل بنا</title>
</head>
<body>
    <h2>رسالة من صفحة اتصل بنا</h2>
    <p><strong>الاسم:</strong> {{ $data['name'] }}</p>
    <p><strong>البريد الإلكتروني:</strong> {{ $data['email'] }}</p>
    
    @if(!empty($data['subject']))
        <p><strong>الموضوع:</strong> {{ $data['subject'] }}</p>
    @endif
    
    <p><strong>الرسالة:</strong></p>
    <p>{{ $data['message'] }}</p>
</body>
</html>
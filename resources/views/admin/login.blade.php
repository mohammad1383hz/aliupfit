<!doctype html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود مدیر | AliUPFit</title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;700;800&display=swap" rel="stylesheet">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f4f8ff;color:#0b1a4a;font:15px/1.8 Vazirmatn,Tahoma,sans-serif;padding:20px}.box{width:min(100%,430px);background:#fff;border:1px solid #d9e4fa;border-radius:22px;padding:30px;box-shadow:0 14px 34px -14px #08144733}h1{margin:0 0 4px;font-size:24px}p{color:#566690;margin:0 0 20px}.field{display:grid;gap:6px;margin:14px 0}label{font-weight:700}input{font:inherit;width:100%;border:1px solid #d9e4fa;border-radius:12px;padding:11px 13px;direction:ltr;text-align:left}button{font:inherit;font-weight:700;border:0;border-radius:12px;background:#1e50ff;color:white;padding:12px 18px;width:100%;cursor:pointer}.error{color:#b42318;margin:10px 0}.back{display:block;text-align:center;margin-top:18px;color:#1e50ff;text-decoration:none}
    </style>
</head>
<body><main class="box">
    <h1>پنل مدیریت AliUPFit</h1><p>برای ادامه با حساب مدیر وارد شوید.</p>
    @if($errors->any())<div class="error" role="alert">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('admin.login.submit') }}">@csrf
        <div class="field"><label for="email">ایمیل مدیر</label><input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="username"></div>
        <div class="field"><label for="password">رمز عبور</label><input id="password" type="password" name="password" required autocomplete="current-password"></div>
        <label style="display:flex;gap:8px;align-items:center;margin:12px 0"><input type="checkbox" name="remember" value="1" style="width:auto"> مرا به خاطر بسپار</label>
        <button type="submit">ورود به پنل</button>
    </form>
    <a class="back" href="{{ route('home') }}">بازگشت به سایت</a>
</main></body></html>

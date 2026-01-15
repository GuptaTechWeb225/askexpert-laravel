<!DOCTYPE html>
<?php
use Illuminate\Support\Facades\Session;
$companyEmail = getWebConfig(name: 'company_email');
$companyName = getWebConfig(name: 'company_name');
$companyLogo = getWebConfig(name: 'company_web_logo');
?>
<html>
<head>
    <meta charset="UTF-8">
    <title>Expert Application Approved</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap');
        body {
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: #e9ecef;
            padding: 15px;
        }
        .main-table {
            width: 500px;
            background: #FFFFFF;
            margin: 0 auto;
            padding: 40px;
        }
        h2 { color: #334257; }
        p { color: #737883; font-size: 14px; }
        .cmn-btn{
            background: #006161;
            color: #fff;
            padding: 10px 22px;
            display: inline-block;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 15px;
        }
        .text-center { text-align: center; }
        hr { margin: 20px 0; }
    </style>
</head>

<body>
<div class="main-table">

    <div class="text-center mb-4">
        <img src="{{ asset($companyLogo) }}" alt="{{ $companyName }}" height="45">
    </div>

    <h2>Hello {{ $expert->f_name }},</h2>

    <p>
        🎉 <strong>Congratulations!</strong><br><br>
        Your expert application on <strong>{{ $companyName }}</strong> has been
        <span style="color:#006161;font-weight:600">approved</span>.
    </p>

    <p>
        You can now log in to your expert account and start accepting questions from users.
    </p>

    <div class="text-center">
        <a href="{{ route('expert.auth.login') }}" class="cmn-btn">
            Login to Expert Panel
        </a>
    </div>

    <hr>

    <p>
        If you have any questions, feel free to contact us at
        <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a>
    </p>

    <p>
        Regards,<br>
        <strong>{{ $companyName }} Team</strong>
    </p>
</div>
</body>
</html>

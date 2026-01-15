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
    <title>Expert Application Update</title>
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
        .reason-box{
            background:#f8f9fa;
            padding:12px;
            border-left:4px solid #dc3545;
            margin:15px 0;
            color:#444;
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
        Thank you for your interest in becoming an expert on
        <strong>{{ $companyName }}</strong>.
    </p>

    <p>
        After careful review, we regret to inform you that your expert application
        has been <span style="color:#dc3545;font-weight:600">rejected</span>.
    </p>

    <div class="reason-box">
        <strong>Reason:</strong><br>
        {{ $reason }}
    </div>

    <p>
        You may update your profile and apply again in the future.
    </p>

    <hr>

    <p>
        If you have any questions, please contact us at
        <a href="mailto:{{ $companyEmail }}">{{ $companyEmail }}</a>
    </p>

    <p>
        Regards,<br>
        <strong>{{ $companyName }} Team</strong>
    </p>
</div>
</body>
</html>

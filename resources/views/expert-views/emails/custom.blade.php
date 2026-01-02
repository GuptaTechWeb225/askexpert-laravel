<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title>{{ $mailData->subject ?? 'Email' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body style="margin:0; padding:0; background:#f9f9f9;">
    @php
        $companyPhone = getWebConfig(name: 'company_phone');
        $companyEmail = getWebConfig(name: 'company_email');
        $companyName  = getWebConfig(name: 'company_name');
        $companyLogo  = getWebConfig(name: 'company_web_logo');
    @endphp

    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
           style="background:#f9f9f9; text-align:center;">
        <tr>
            <td align="center">
                <!-- Main Card -->
                <table role="presentation" width="600" cellspacing="0" cellpadding="0" border="0"
                       style="background:#fff; margin:20px auto; padding:20px; border-radius:6px; text-align:center;">
                    <tr>
                        <td>
                            @if($imageUrl)
                                <div style="margin-bottom:20px;">
                                    <img src="{{ $imageUrl }}" alt="Mail Image"
                                         style="max-width:150px; height:auto; display:block; margin:0 auto;">
                                </div>
                            @endif
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px;">
                            <h2 style="margin:0; font-size:20px; color:#333;">{{ $mailData->subject ?? '' }}</h2>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px; font-size:14px; color:#444;">
                            <p>Hello {{ $user->name ?? 'Customer' }},</p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px; font-size:14px; color:#444;">
                            {!! $mailData->body ?? '' !!}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:10px; font-size:14px; color:#444;">
                            From Restaurant:- {{ $restaurant->restaurant_name }}
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px; border-top:1px solid #eee; text-align:center;">
                            <img src="{{ getStorageImages(path: $companyLogo, type: 'backend-logo') }}"
                                 alt="{{ $companyName }}"
                                 style="max-width:60px; height:auto; margin-bottom:10px; display:block; margin-left:auto; margin-right:auto;">

                            <p style="margin:0; font-weight:bold; font-size:14px; color:#333;">
                                {{ $companyName }}
                            </p>
                            <p style="margin:0; font-size:13px; color:#555;">
                                Email: {{ $companyEmail }}
                            </p>
                            <p style="margin:0; font-size:13px; color:#555;">
                                Phone: {{ $companyPhone }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</body>
</html>

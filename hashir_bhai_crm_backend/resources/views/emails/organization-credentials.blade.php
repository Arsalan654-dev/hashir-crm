@php
    $appName = config('app.name', 'CRM');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $organization->name }} — Your CRM account</title>
</head>
<body style="margin:0;padding:0;background-color:#f3f6f9;font-family:Arial,Helvetica,sans-serif;color:#343a40;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f3f6f9;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 6px rgba(0,0,0,0.06);">
                <tr>
                    <td style="background-color:#405189;padding:28px 32px;">
                        <h1 style="margin:0;font-size:22px;color:#ffffff;">{{ $appName }}</h1>
                        <p style="margin:6px 0 0;font-size:14px;color:#d4d9ee;">Your CRM is ready to go live</p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:32px;">
                        <p style="margin:0 0 16px;font-size:15px;">Hi {{ $owner->name }},</p>
                        <p style="margin:0 0 16px;font-size:15px;line-height:1.6;">
                            Your CRM account for <strong>{{ $organization->name }}</strong>
                            ({{ $organization->businessTypeLabel() }}) has been created.
                            Below are your login details.
                        </p>

                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8f9fc;border:1px solid #e9ebec;border-radius:6px;margin:0 0 20px;">
                            <tr>
                                <td style="padding:18px 20px;font-size:14px;line-height:1.9;">
                                    <strong>Login URL:</strong>
                                    <a href="{{ $loginUrl }}" style="color:#405189;">{{ $loginUrl }}</a><br>
                                    <strong>Username:</strong> {{ $owner->username }}<br>
                                    <strong>Email:</strong> {{ $owner->email }}<br>
                                    @if (filled($password))
                                        <strong>Temporary password:</strong> {{ $password }}
                                    @else
                                        <strong>Password:</strong> use the password provided to you. If you forgot it, please contact support.
                                    @endif
                                </td>
                            </tr>
                        </table>

                        @if ((float) $organization->plan_amount > 0)
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#fff8e6;border:1px solid #ffe7a3;border-radius:6px;margin:0 0 20px;">
                                <tr>
                                    <td style="padding:16px 20px;font-size:14px;line-height:1.7;">
                                        <strong>Activation:</strong>
                                        To activate your WhatsApp bot and full features, please complete your payment of
                                        <strong>{{ number_format((float) $organization->plan_amount, 2) }} {{ $organization->currency }}</strong>
                                        ({{ str_replace('_', ' ', $organization->billing_cycle) }} plan).
                                        Payment instructions are available inside your CRM on the Billing page.
                                    </td>
                                </tr>
                            </table>
                        @endif

                        <p style="margin:0 0 8px;font-size:15px;">Next steps:</p>
                        <ol style="margin:0 0 20px;padding-left:20px;font-size:14px;line-height:1.9;">
                            <li>Log in and change your password from your profile.</li>
                            <li>Complete the payment to activate all features.</li>
                            <li>Add your products, connect WhatsApp and train your bot.</li>
                        </ol>

                        <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 8px;">
                            <tr>
                                <td style="background-color:#0ab39c;border-radius:4px;">
                                    <a href="{{ $loginUrl }}" style="display:inline-block;padding:12px 26px;font-size:14px;color:#ffffff;text-decoration:none;">Log in to your CRM</a>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:18px 32px;background-color:#f8f9fc;border-top:1px solid #e9ebec;">
                        <p style="margin:0;font-size:12px;color:#878a99;">
                            This email was sent by {{ $appName }}. If you need help, contact our support team.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f4f7;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="background:#4CAF50; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px;">E-Serbisyo Barangay Santol Support</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:30px;">
                            <h2 style="margin-top:0; color:#333;">Password Reset Request</h2>
                            <p style="color:#555; font-size:15px;">
                                Hello, {{ $account->first_name }} {{ $account->last_name }},<br><br>
                                We received a request to reset your password. Your new password is shown below. Please log in and change it as soon as possible.
                            </p>
                            <table cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse; margin:20px 0;">
                                <tr style="background:#f9f9f9;">
                                    <td style="width:150px; font-weight:bold;">Email:</td>
                                    <td>{{ $account->email }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">New Password:</td>
                                    <td style="color:#e74c3c; font-weight:bold;">{{ $password }}</td>
                                </tr>
                            </table>
                            <p style="color:#888; font-size:13px;">
                                If you did not request this password reset, please contact support immediately.
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background:#f4f4f7; padding:20px; text-align:center; color:#888; font-size:12px;">
                            &copy; {{ date('Y') }} E-Serbisyo Barangay Santol. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

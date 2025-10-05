<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Approved</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f4f7;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#4CAF50; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px;">E-Serbisyo Barangay Santol</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">
                            <div style="text-align:center; margin-bottom:20px;">
                                <div style="background:#4CAF50; color:white; padding:10px 20px; border-radius:25px; display:inline-block; font-weight:bold;">
                                    ✓ ACCOUNT APPROVED
                                </div>
                            </div>

                            <h2 style="margin-top:0; color:#333; text-align:center;">Congratulations, {{ $account->first_name }} {{ $account->last_name }}!</h2>
                            <p style="color:#555; font-size:15px; text-align:center;">
                                Your account has been <strong>approved</strong> and you can now access all E-Serbisyo services.
                            </p>

                            <table cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse; margin:20px 0;">
                                <tr style="background:#f9f9f9;">
                                    <td style="width:150px; font-weight:bold;">Email:</td>
                                    <td>{{ $account->email }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Full Name:</td>
                                    <td>{{ $account->first_name }} {{ $account->middle_name }} {{ $account->last_name }} {{ $account->suffix }}</td>
                                </tr>
                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Account Type:</td>
                                    <td>{{ ucfirst($account->type) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Status:</td>
                                    <td><span style="color:#4CAF50; font-weight:bold;">Active</span></td>
                                </tr>
                            </table>

                            <div style="background:#e8f5e8; padding:20px; border-radius:8px; border-left:4px solid #4CAF50; margin:20px 0;">
                                <h3 style="margin-top:0; color:#2e7d32;">What's Next?</h3>
                                <ul style="color:#555; margin:0; padding-left:20px;">
                                    <li>You can now log in to your account</li>
                                    <li>Access barangay services and documents</li>
                                    <li>Submit requests and track their status</li>
                                    <li>Stay updated with announcements</li>
                                </ul>
                            </div>

                            <div style="text-align:center; margin:30px 0;">
                                <a href="#" style="background:#4CAF50; color:white; padding:12px 30px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block;">
                                    Login to Your Account
                                </a>
                            </div>

                            <p style="margin-top:30px; color:#333;">
                                Welcome to the E-Serbisyo family!<br>
                                <strong>The Admin Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f4f4f7; text-align:center; padding:15px; font-size:12px; color:#888;">
                            &copy; {{ date('Y') }} Barangay E-Serbisyo. All rights reserved.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>

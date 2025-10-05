<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Registration Update</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f4f7;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#f44336; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px;">E-Serbisyo Barangay Santol</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">
                            <div style="text-align:center; margin-bottom:20px;">
                                <div style="background:#f44336; color:white; padding:10px 20px; border-radius:25px; display:inline-block; font-weight:bold;">
                                    ✗ REGISTRATION DECLINED
                                </div>
                            </div>

                            <h2 style="margin-top:0; color:#333; text-align:center;">Dear {{ $rejectedAccount->first_name }} {{ $rejectedAccount->last_name }},</h2>
                            <p style="color:#555; font-size:15px; text-align:center;">
                                We regret to inform you that your account registration has been declined.
                            </p>

                            <div style="background:#ffebee; padding:20px; border-radius:8px; border-left:4px solid #f44336; margin:20px 0;">
                                <h3 style="margin-top:0; color:#c62828;">Reason for Rejection:</h3>
                                <p style="color:#555; margin:0; font-size:15px;">
                                    {{ $rejectedAccount->reason }}
                                </p>
                            </div>

                            <table cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse; margin:20px 0;">
                                <tr style="background:#f9f9f9;">
                                    <td style="width:150px; font-weight:bold;">Email:</td>
                                    <td>{{ $rejectedAccount->email }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Full Name:</td>
                                    <td>{{ $rejectedAccount->first_name }} {{ $rejectedAccount->middle_name }} {{ $rejectedAccount->last_name }} {{ $rejectedAccount->suffix }}</td>
                                </tr>
                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Submitted Date:</td>
                                    <td>{{ $rejectedAccount->created_at->format('F d, Y') }}</td>
                                </tr>
                            </table>

                            <div style="background:#fff3e0; padding:20px; border-radius:8px; border-left:4px solid #ff9800; margin:20px 0;">
                                <h3 style="margin-top:0; color:#ef6c00;">What can you do?</h3>
                                <ul style="color:#555; margin:0; padding-left:20px;">
                                    <li>Review the reason for rejection above</li>
                                    <li>Address the concerns mentioned</li>
                                    <li>Submit a new registration with corrected information</li>
                                    <li>Contact our support team if you need assistance</li>
                                </ul>
                            </div>

                            <div style="text-align:center; margin:30px 0;">
                                <a href="#" style="background:#2196F3; color:white; padding:12px 30px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block;">
                                    Register Again
                                </a>
                            </div>

                            <p style="color:#555; font-size:14px; text-align:center; font-style:italic;">
                                If you believe this decision was made in error, please contact our support team for further assistance.
                            </p>

                            <p style="margin-top:30px; color:#333;">
                                Thank you for your understanding,<br>
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

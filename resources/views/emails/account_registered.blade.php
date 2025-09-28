<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Account Registration</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f4f7;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#4CAF50; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px;">E-Serbisyo Barangay Santol Support</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">
                            <h2 style="margin-top:0; color:#333;">Welcome, {{ $account->first_name }} {{ $account->last_name }}!</h2>
                            <p style="color:#555; font-size:15px;">
                                Your account has been successfully registered with the following details:
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
                                    <td style="font-weight:bold;">Sex:</td>
                                    <td>{{ ucfirst($account->sex) }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Birthday:</td>
                                    <td>{{ $account->birthday }}</td>
                                </tr>
                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Contact No:</td>
                                    <td>{{ $account->contact_no }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Address:</td>
                                    <td>{{ $account->house_no }}, {{ $account->street }}, {{ $account->barangay }}, {{ $account->municipality }}, {{ $account->zip_code }}</td>
                                </tr>
                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Type:</td>
                                    <td>{{ ucfirst($account->type) }}</td>
                                </tr>
                            </table>

                            <p style="color:#555; font-size:15px;">
                                You can now log in using your registered email.
                            </p>

                            <p style="margin-top:30px; color:#333;">
                                Thank you,<br>
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

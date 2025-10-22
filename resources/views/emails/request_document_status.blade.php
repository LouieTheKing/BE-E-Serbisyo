<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Document Request Status Update</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f4f7;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background:{{ $status === 'approved' || $status === 'released' ? '#4CAF50' : ($status === 'rejected' ? '#f44336' : '#2196F3') }}; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px;">E-Serbisyo Barangay Santol</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">
                            <div style="text-align:center; margin-bottom:20px;">
                                <div style="background:{{ $status === 'approved' || $status === 'released' ? '#4CAF50' : ($status === 'rejected' ? '#f44336' : '#2196F3') }}; color:white; padding:10px 20px; border-radius:25px; display:inline-block; font-weight:bold;">
                                    @if($status === 'approved')
                                        ✓ APPROVED
                                    @elseif($status === 'released')
                                        ✓ RELEASED
                                    @elseif($status === 'rejected')
                                        ✕ REJECTED
                                    @elseif($status === 'processing')
                                        ⟳ PROCESSING
                                    @elseif($status === 'ready to pickup')
                                        ✓ READY TO PICKUP
                                    @else
                                        ● {{ strtoupper($status) }}
                                    @endif
                                </div>
                            </div>

                            <h2 style="margin-top:0; color:#333; text-align:center;">
                                Hello, {{ $requestDocument->account->first_name ?? 'User' }} {{ $requestDocument->account->last_name ?? '' }}!
                            </h2>
                            <p style="color:#555; font-size:15px; text-align:center;">
                                Your document request status has been updated to <strong>{{ ucfirst(str_replace('_', ' ', $status)) }}</strong>.
                            </p>

                            <table cellpadding="8" cellspacing="0" width="100%" style="border-collapse:collapse; margin:20px 0;">
                                <tr style="background:#f9f9f9;">
                                    <td style="width:180px; font-weight:bold;">Transaction ID:</td>
                                    <td><strong>{{ $requestDocument->transaction_id ?? 'N/A' }}</strong></td>
                                </tr>
                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Document Type:</td>
                                    <td>{{ $requestDocument->documentDetails->document_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td style="font-weight:bold;">Request Date:</td>
                                    <td>{{ $requestDocument->created_at->format('F d, Y h:i A') }}</td>
                                </tr>
                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Status:</td>
                                    <td>
                                        <span style="color:{{ $status === 'approved' || $status === 'released' ? '#4CAF50' : ($status === 'rejected' ? '#f44336' : '#2196F3') }}; font-weight:bold;">
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                            @if($status === 'approved')
                            <div style="background:#e8f5e8; padding:20px; border-radius:8px; border-left:4px solid #4CAF50; margin:20px 0;">
                                <h3 style="margin-top:0; color:#2e7d32;">What's Next?</h3>
                                <ul style="color:#555; margin:0; padding-left:20px;">
                                    <li>Your request has been approved</li>
                                    <li>Your document is being processed</li>
                                    <li>You will be notified when it's ready for pickup</li>
                                </ul>
                            </div>
                            @elseif($status === 'processing')
                            <div style="background:#e3f2fd; padding:20px; border-radius:8px; border-left:4px solid #2196F3; margin:20px 0;">
                                <h3 style="margin-top:0; color:#1565c0;">What's Next?</h3>
                                <ul style="color:#555; margin:0; padding-left:20px;">
                                    <li>Your document is currently being processed</li>
                                    <li>Please wait for further updates</li>
                                    <li>You will be notified when it's ready</li>
                                </ul>
                            </div>
                            @elseif($status === 'ready to pickup')
                            <div style="background:#e8f5e8; padding:20px; border-radius:8px; border-left:4px solid #4CAF50; margin:20px 0;">
                                <h3 style="margin-top:0; color:#2e7d32;">Ready for Pickup!</h3>
                                <ul style="color:#555; margin:0; padding-left:20px;">
                                    <li>Your document is ready for pickup</li>
                                    <li>Please visit the barangay office during office hours</li>
                                    <li>Bring a valid ID for verification</li>
                                </ul>
                            </div>
                            @elseif($status === 'released')
                            <div style="background:#e8f5e8; padding:20px; border-radius:8px; border-left:4px solid #4CAF50; margin:20px 0;">
                                <h3 style="margin-top:0; color:#2e7d32;">Document Released!</h3>
                                <ul style="color:#555; margin:0; padding-left:20px;">
                                    <li>Your document has been successfully released</li>
                                    <li>Thank you for using E-Serbisyo</li>
                                    <li>We hope to serve you again</li>
                                </ul>
                            </div>
                            @elseif($status === 'rejected')
                            <div style="background:#ffebee; padding:20px; border-radius:8px; border-left:4px solid #f44336; margin:20px 0;">
                                <h3 style="margin-top:0; color:#c62828;">Request Rejected</h3>
                                <ul style="color:#555; margin:0; padding-left:20px;">
                                    <li>Unfortunately, your request has been rejected</li>
                                    <li>Please contact the barangay office for more information</li>
                                    <li>You may submit a new request with the correct requirements</li>
                                </ul>
                            </div>
                            @endif

                            <div style="text-align:center; margin:30px 0;">
                                <a href="https://santol-serbisyo.org/track-document" style="background:{{ $status === 'approved' || $status === 'released' ? '#4CAF50' : ($status === 'rejected' ? '#f44336' : '#2196F3') }}; color:white; padding:12px 30px; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-block;">
                                    View Request Details
                                </a>
                            </div>

                            <p style="margin-top:30px; color:#333;">
                                If you have any questions, please contact us.<br>
                                <strong>The E-Serbisyo Team</strong>
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

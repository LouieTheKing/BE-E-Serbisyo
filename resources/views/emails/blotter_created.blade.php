<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Blotter Case Filed</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f4f7;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#2196F3; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px;">E-Serbisyo Barangay Santol</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">
                            <div style="text-align:center; margin-bottom:20px;">
                                <div style="background:#2196F3; color:white; padding:10px 20px; border-radius:25px; display:inline-block; font-weight:bold;">
                                    📋 BLOTTER CASE FILED
                                </div>
                            </div>

                            <h2 style="color:#333; margin:0 0 20px 0;">New Blotter Case Filed</h2>
                            
                            <p style="color:#666; font-size:16px; line-height:1.6; margin:0 0 20px 0;">
                                A new blotter case has been filed in the system. Here are the details:
                            </p>

                            <div style="background:#f8f9fa; padding:20px; border-radius:8px; margin:20px 0;">
                                <table width="100%" cellpadding="8" cellspacing="0" style="border-collapse:collapse;">
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef; width:30%;">Case Number:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">{{ $blotter->case_number }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Case Type:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">{{ $blotter->case_type }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Complainant:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">{{ $blotter->complainant_name }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Respondent:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">{{ $blotter->respondent_name }}</td>
                                    </tr>
                                    @if($blotter->additional_respondent && count($blotter->additional_respondent) > 0)
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Additional Respondents:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">{{ implode(', ', $blotter->additional_respondent) }}</td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Date Filed:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">{{ $blotter->date_filed->format('F j, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Status:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">
                                            <span style="background:#2196F3; color:white; padding:4px 12px; border-radius:12px; font-size:12px;">
                                                {{ strtoupper($blotter->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Received By:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">{{ $blotter->received_by }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold; color:#333;">Filed By:</td>
                                        <td style="color:#666;">{{ $blotter->createdBy->first_name }} {{ $blotter->createdBy->last_name }}</td>
                                    </tr>
                                </table>
                            </div>

                            <div style="background:#fff3cd; border:1px solid #ffeaa7; padding:15px; border-radius:8px; margin:20px 0;">
                                <h4 style="color:#856404; margin:0 0 10px 0;">Complaint Details:</h4>
                                <p style="color:#856404; margin:0; line-height:1.6;">{{ $blotter->complaint_details }}</p>
                            </div>

                            <div style="background:#d1ecf1; border:1px solid #bee5eb; padding:15px; border-radius:8px; margin:20px 0;">
                                <h4 style="color:#0c5460; margin:0 0 10px 0;">Relief Sought:</h4>
                                <p style="color:#0c5460; margin:0; line-height:1.6;">{{ $blotter->relief_sought }}</p>
                            </div>

                            <p style="color:#666; font-size:14px; line-height:1.6; margin:20px 0 0 0;">
                                This is an automated notification. Please contact the barangay office for any inquiries regarding this case.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f8f9fa; padding:20px; text-align:center; border-top:1px solid #e9ecef;">
                            <p style="margin:0; color:#666; font-size:12px;">
                                E-Serbisyo Barangay Santol<br>
                                Digital Services Platform<br>
                                <strong>Date Generated:</strong> {{ now()->format('F j, Y g:i A') }}
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Sumbong Status Update</title>
</head>
<body style="margin:0; padding:0; font-family: Arial, Helvetica, sans-serif; background-color:#f4f4f7;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f7; padding:20px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.1);">

                    <!-- Header -->
                    <tr>
                        <td style="background:{{ $status === 'settled' ? '#4CAF50' : ($status === 'ongoing' ? '#FF9800' : ($status === 'reopen' ? '#9C27B0' : ($status === 'unsettled' ? '#f44336' : '#2196F3'))) }}; padding:20px; text-align:center; color:#ffffff;">
                            <h1 style="margin:0; font-size:24px;">E-Serbisyo Barangay Santol</h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">
                            <div style="text-align:center; margin-bottom:20px;">
                                <div style="background:{{ $status === 'settled' ? '#4CAF50' : ($status === 'ongoing' ? '#FF9800' : ($status === 'reopen' ? '#9C27B0' : ($status === 'unsettled' ? '#f44336' : '#2196F3'))) }}; color:white; padding:10px 20px; border-radius:25px; display:inline-block; font-weight:bold;">
                                    @if($status === 'settled')
                                        ✓ CASE SETTLED
                                    @elseif($status === 'ongoing')
                                        🔄 CASE ONGOING
                                    @elseif($status === 'reopen')
                                        🔄 CASE REOPENED
                                    @elseif($status === 'unsettled')
                                        ⚠️ CASE UNSETTLED
                                    @elseif($status === 'filed')
                                        📋 CASE FILED
                                    @else
                                        📊 STATUS UPDATE
                                    @endif
                                </div>
                            </div>

                            <h2 style="color:#333; margin:0 0 20px 0;">Sumbong Status Update</h2>
                            
                            <p style="color:#666; font-size:16px; line-height:1.6; margin:0 0 20px 0;">
                                The status of your sumbong case has been updated. Here are the current details:
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
                                    @if($oldStatus)
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Previous Status:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">
                                            <span style="background:#6c757d; color:white; padding:4px 12px; border-radius:12px; font-size:12px;">
                                                {{ strtoupper($oldStatus) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Current Status:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">
                                            <span style="background:{{ $status === 'settled' ? '#4CAF50' : ($status === 'ongoing' ? '#FF9800' : ($status === 'reopen' ? '#9C27B0' : ($status === 'unsettled' ? '#f44336' : '#2196F3'))) }}; color:white; padding:4px 12px; border-radius:12px; font-size:12px;">
                                                {{ strtoupper($status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold; color:#333; border-bottom:1px solid #e9ecef;">Date Filed:</td>
                                        <td style="color:#666; border-bottom:1px solid #e9ecef;">{{ $blotter->date_filed->format('F j, Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-weight:bold; color:#333;">Last Updated:</td>
                                        <td style="color:#666;">{{ $blotter->updated_at->format('F j, Y g:i A') }}</td>
                                    </tr>
                                </table>
                            </div>

                            @if($status === 'settled')
                                <div style="background:#d4edda; border:1px solid #c3e6cb; padding:15px; border-radius:8px; margin:20px 0;">
                                    <h4 style="color:#155724; margin:0 0 10px 0;">✓ Case Resolution</h4>
                                    <p style="color:#155724; margin:0; line-height:1.6;">This case has been successfully resolved and settled. All parties have reached an agreement.</p>
                                </div>
                            @elseif($status === 'ongoing')
                                <div style="background:#fff3cd; border:1px solid #ffeaa7; padding:15px; border-radius:8px; margin:20px 0;">
                                    <h4 style="color:#856404; margin:0 0 10px 0;">🔄 Case in Progress</h4>
                                    <p style="color:#856404; margin:0; line-height:1.6;">This case is currently being processed. Please wait for further updates.</p>
                                </div>
                            @elseif($status === 'reopen')
                                <div style="background:#f3e5f5; border:1px solid #e1bee7; padding:15px; border-radius:8px; margin:20px 0;">
                                    <h4 style="color:#4a148c; margin:0 0 10px 0;">🔄 Case Reopened</h4>
                                    <p style="color:#4a148c; margin:0; line-height:1.6;">This case has been reopened for further investigation and processing.</p>
                                </div>
                            @elseif($status === 'unsettled')
                                <div style="background:#f8d7da; border:1px solid #f5c6cb; padding:15px; border-radius:8px; margin:20px 0;">
                                    <h4 style="color:#721c24; margin:0 0 10px 0;">⚠️ Case Unsettled</h4>
                                    <p style="color:#721c24; margin:0; line-height:1.6;">This case remains unresolved. Further action may be required.</p>
                                </div>
                            @endif

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

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lead Follow-up Reminder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 0 0 5px 5px;
        }
        .lead-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .lead-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .lead-info td {
            padding: 5px 0;
        }
        .lead-info td:first-child {
            font-weight: bold;
            width: 40%;
        }
        .action-button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #007bff;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Lead Follow-up Reminder</h2>
        <p>It's been 7 days since this lead was created</p>
    </div>
    
    <div class="content">
        <p>Dear {{ $lead->assignedTo->name ?? 'Team Member' }},</p>
        
        <p>This is a reminder to follow up on the lead below. It has been 7 days since the lead was created and may require your attention.</p>
        
        <div class="lead-info">
            <h3>Lead Information</h3>
            <table>
                <tr>
                    <td>Lead No:</td>
                    <td>{{ $lead->lead_no }}</td>
                </tr>
                <tr>
                    <td>Temple Name:</td>
                    <td>{{ $lead->company_name ?: 'Not specified' }}</td>
                </tr>
                <tr>
                    <td>Contact Person:</td>
                    <td>{{ $lead->contact_person }}</td>
                </tr>
                <tr>
                    <td>Email:</td>
                    <td>{{ $lead->email ?: 'Not provided' }}</td>
                </tr>
                <tr>
                    <td>Phone:</td>
                    <td>{{ $lead->mobile ?: $lead->phone ?: 'Not provided' }}</td>
                </tr>
                <tr>
                    <td>Location:</td>
                    <td>{{ $lead->city ?: 'Not specified' }}</td>
                </tr>
                <tr>
                    <td>Temple Category:</td>
                    <td>{{ $lead->templeCategory->name ?? 'Not categorized' }}</td>
                </tr>
                <tr>
                    <td>Current Status:</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $lead->lead_status)) }}</td>
                </tr>
                <tr>
                    <td>Lead Age:</td>
                    <td>{{ $lead->created_at->diffInDays() }} days</td>
                </tr>
            </table>
        </div>
        
        @if($lead->interested_in)
        <div class="lead-info">
            <h4>Interested In:</h4>
            <p>{{ $lead->interested_in }}</p>
        </div>
        @endif
        
        @if($lead->notes)
        <div class="lead-info">
            <h4>Notes:</h4>
            <p>{{ $lead->notes }}</p>
        </div>
        @endif
        
        <h3>Recommended Actions:</h3>
        <ul>
            <li>Review the lead details and previous activities</li>
            <li>Contact the temple to understand their requirements</li>
            <li>Schedule a demo or meeting if appropriate</li>
            <li>Update the lead status based on your interaction</li>
        </ul>
        
        <p style="text-align: center;">
            <a href="{{ url('/leads/' . $lead->id) }}" class="action-button">View Lead Details</a>
        </p>
        
        <p>Please take appropriate action to move this lead forward in the sales process.</p>
        
        <p>Best regards,<br>
        Temple Management System</p>
    </div>
    
    <div class="footer">
        <p>This is an automated reminder from your Temple Management CRM system.</p>
        <p>© {{ date('Y') }} Temple Management Software. All rights reserved.</p>
    </div>
</body>
</html>
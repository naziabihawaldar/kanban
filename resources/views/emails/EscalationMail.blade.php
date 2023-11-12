<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Due Date Missed</title>
</head>
<body>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="background-color: #f4f4f4; padding: 20px; text-align: center;">
                            <h1>Task Due Date Missed</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px;">
                            <p>Hello {{$username}},</p>
                            <p>We regret to inform you that the due date for a task assigned to {{ $assignedToName }} has passed. Here are the task details:</p>

                            <ul>
                                <li><strong>Task Name:</strong>{{ $taskTitle }}</li>
                                {{-- <li><strong>Description:</strong> [Task Description]</li>
                                <li><strong>Due Date:</strong> [Task Due Date]</li> --}}
                            </ul>

                            <p>Please take the necessary steps to address this situation and ensure the completion of the task. You may want to reach out to {{ $assignedToName }} for an update or to discuss any issues they may be facing.</p>

                            <p>Thank you for your attention to this matter and for your commitment to our team's success.</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="background-color: #f4f4f4; padding: 20px; text-align: center;">
                            &copy; [Year] [Your Company Name]
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

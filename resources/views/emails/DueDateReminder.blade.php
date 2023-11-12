<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Task Due Tomorrow</title>
</head>
<body>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse;">
                    <tr>
                        <td style="background-color: #f4f4f4; padding: 20px; text-align: center;">
                            <h1>Task Due Tomorrow</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding: 20px;">
                            <p>Hello {{$username}},</p>
                            <p>This is a friendly reminder that your task is due tomorrow. Here are the details:</p>

                            <ul>
                                <li><strong>Task Name:</strong> {{$taskTitle}}</li>
                                {{-- <li><strong>Description:</strong> [Task Description]</li>
                                <li><strong>Due Date:</strong> [Task Due Date]</li> --}}
                            </ul>

                            <p>Please ensure that you complete this task on time. If you have any questions or need further information, please don't hesitate to ask. We are here to support you.</p>

                            <p>Thank you for your dedication and commitment to our team's success.</p>
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

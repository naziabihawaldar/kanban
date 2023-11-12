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
                            <p>Hello {{ $username }},</p>
                            <p>We want to inform you that a task with a due date of tomorrow requires attention. Here are the task details:</p>

                            <ul>
                                <li><strong>Task Name:</strong> {{ $taskTitle }}</li>
                                <li><strong>Assigned To:</strong> {{ $assignedToName }}</li>
                            </ul>

                            <p>Please ensure that this task is on track for completion or take necessary steps to address any issues that may arise. You may want to reach out to the responsible person for an update.</p>

                            <p>Thank you for your commitment to our team's success.</p>
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
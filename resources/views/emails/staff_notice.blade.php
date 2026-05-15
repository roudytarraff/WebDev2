<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; margin: 0; padding: 0; }
        .wrap { max-width: 600px; margin: 40px auto; background: #fff; border-radius: 8px; overflow: hidden; }
        .header { background: #0f172a; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 20px; }
        .body { padding: 28px 32px; color: #374151; line-height: 1.6; }
        .box { background: #f8fafc; border-radius: 6px; padding: 16px; margin: 16px 0; border-left: 4px solid #0f172a; }
        .footer { padding: 16px 32px; background: #f9fafb; color: #9ca3af; font-size: 12px; }
    </style>
</head>
<body>
<div class="wrap">
    <div class="header">
        <h1>{{ $notification->title }}</h1>
    </div>
    <div class="body">
        <p>Hello {{ $notification->user->full_name ?? 'Team Member' }},</p>
        <div class="box">
            <p>{{ $notification->message }}</p>
        </div>
        <p>Log in to the portal to view more details.</p>
    </div>
    <div class="footer">
        <p>This is an automated message from the E-Services Portal. Please do not reply.</p>
    </div>
</div>
</body>
</html>

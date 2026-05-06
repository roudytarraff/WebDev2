<!DOCTYPE html>
<html>
<head>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta charset="UTF-8">
    <title>OTP Verification</title>
</head>

<body style="margin:0; padding:0; font-family:Arial; background:#f4f4f4;">

<div style="max-width:420px; margin:40px auto; background:#fff; padding:25px; border-radius:10px; text-align:center;">

    <h2 style="margin-bottom:10px;">Your Verification Code</h2>

    <p style="color:#666;">Use the code below to verify your account:</p>

    <div style="
        font-size:32px;
        letter-spacing:8px;
        font-weight:bold;
        margin:20px 0;
        padding:15px;
        background:#f0f0f0;
        border-radius:8px;
    ">
        {{ $otp }}
    </div>

    <p style="font-size:12px; color:gray;">
        This code expires in 10 minutes.
    </p>

</div>

</body>
</html>
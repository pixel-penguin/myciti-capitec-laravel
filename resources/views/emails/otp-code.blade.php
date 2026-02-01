<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>OTP</title>
</head>
<body>
    <p>Your one-time PIN is:</p>
    <h2>{{ $code }}</h2>
    <p>This code expires in {{ $minutes }} minutes.</p>
    <p>If you did not request this, please ignore this email.</p>
</body>
</html>

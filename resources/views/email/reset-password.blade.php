<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Reset Passwod Email</title>
</head>

<body style="font-family:Arial, Helvetica, sans-serif; font-size:16px;">

    <p>Hello, {{ $formData['user']->name }}</p>
    <h1>You have requested to change password.</h1>
    <p>please click the link given below to reset password</p>

    <a href="{{ route('fornt.resetPassword',$formData['token']) }}">click here</a>

</body>

</html>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Send Mail</title>
</head>
<body>
    <p>Dear {{$mailData['name']}}, </p>
    <p></p>
    <p>{{$mailData['body']}}</p>
    <p>Please, log in to the app to approve the request.</p>
    <p>Thank you</p>

</body>
</html>
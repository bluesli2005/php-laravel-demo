<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Top page</title>
    </head>
    <body>
        <h1>Home</h1>
        <form action="{{ route('welcome') }}" method="get">
            <button type="submit">前往 Welcome 页面</button>
        </form>
    </body>
</html>

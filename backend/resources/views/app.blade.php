<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name') }}</title>
    <link rel="icon" type="image/png" href="/logo.png">
    <link rel="apple-touch-icon" href="/logo.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @vite(['src/app.css', 'src/app.js'])
</head>
<body>
    <div id="app"></div>
</body>
</html>
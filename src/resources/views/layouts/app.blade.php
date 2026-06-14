<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>post_md</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="pmd-body">
    @yield('content')
</body>
</html>

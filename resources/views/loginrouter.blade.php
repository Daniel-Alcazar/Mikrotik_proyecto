<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="{{ asset('css/LoginForm.css') }}">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="wrapper">
        <form action="{{ route('router.login') }}" method="POST">
            @csrf
            <h1>RouterOs</h1>

            <div class="input-box">
                <input type="text" name="login" placeholder="username" required>
                <i class="fa fa-user icon"></i>
            </div>

            <div class="input-box">
                <input type="password" name="password" placeholder="PASSWORD" required>
                <i class="fa fa-lock icon"></i>
            </div>

            <div class="input-box">
                <input type="text" name="ip_address" placeholder="IP" required>
                <i class="md-flip-camera-android icon"></i>
            </div>



            <button type="submit">Login</button>

 
        </form>
    </div>

    <!-- Aquí puedes agregar tus scripts, si es necesario -->
    <script src="https://cdn.jsdelivr.net/npm/react-icons/fa"></script>
    <script src="https://cdn.jsdelivr.net/npm/react-icons/md"></script>
</body>
</htm
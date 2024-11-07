<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>

<div class="sidebar">
    <a onclick="showSection('ip')">Agregar IP</a>
    <a onclick="showSection('user')">Agregar Usuario</a>
    <a onclick="showSection('bandwidth')">Ancho de Banda</a>
    <a onclick="showSection('newAddress')">New Address</a>
    <a onclick="showSection('ipRoute')">IP Route</a>
    <a onclick="showSection('dnsServer')">Agregar DNS Server</a>
</div>

<div class="content">
    <div id="default" style="display: block;">
        <h2>Bienvenido al Dashboard</h2>
        <p>Selecciona una opción a la izquierda para empezar.</p>
    </div>

    <div id="ip" style="display: none;">
        <h2>Agregar IP</h2>
        <form>
            <label for="ip">IP:</label>
            <input type="text" id="ip" name="ip">
            <button type="submit">Guardar IP</button>
        </form>
    </div>

    <div id="user" style="display: none;">
        <h2>Agregar Usuario</h2>
        <form>
            <label for="ip">IP:</label>
            <input type="text" id="ip" name="ip">

            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username">

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password">

            <label for="profile">Perfil:</label>
            <input type="text" id="profile" name="profile">

            <button type="submit">Guardar Usuario</button>
        </form>
    </div>

    <div id="bandwidth" style="display: none;">
        <h2>Ancho de Banda</h2>
        <form>
            <label for="ip">IP:</label>
            <input type="text" id="ip" name="ip">

            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username">

            <label for="downloadLimit">Download Limit (Mbps):</label>
            <input type="text" id="downloadLimit" name="downloadLimit">

            <label for="uploadLimit">Upload Limit (Mbps):</label>
            <input type="text" id="uploadLimit" name="uploadLimit">

            <button type="submit">Guardar Ancho de Banda</button>
        </form>
    </div>

    <div id="newAddress" style="display: none;">
        <h2>New Address</h2>
        <form>
            <label for="ip">IP:</label>
            <input type="text" id="ip" name="ip">

            <label for="address">Address:</label>
            <input type="text" id="address" name="address">

            <label for="interface">Interface:</label>
            <input type="text" id="interface" name="interface">

            <button type="submit">Guardar Address</button>
        </form>
    </div>

    <div id="ipRoute" style="display: none;">
        <h2>IP Route</h2>
        <form method="POST" action="{{ route('add_ip_route') }}">
          @csrf
            <input type="hidden" class="form-control" id="ip_address" name="ip_address" value="{{ session('ip_address') }}">
            
            <label for="gateway">Gateway:</label>
            <input type="text" id="gateway" name="gateway">

            <button type="submit">Guardar IP Route</button>
        </form>
    </div>

    <div id="dnsServer" style="display: none;">
        <h2>Agregar DNS Server</h2>
        <form>
            <label for="ip">IP:</label>
            <input type="text" id="ip" name="ip">

            <label for="servers">Servers:</label>
            <input type="text" id="servers" name="servers">

            <label for="remoteRequests">Remote Requests:</label>
            <input type="text" id="remoteRequests" name="remoteRequests">

            <button type="submit">Guardar DNS Server</button>
        </form>
    </div>
</div>

<script>
    function showSection(section) {
        const sections = ["default", "ip", "user", "bandwidth", "newAddress", "ipRoute", "dnsServer"];
        sections.forEach(s => {
            document.getElementById(s).style.display = s === section ? 'block' : 'none';
        });
    }
</script>

</body>
</html>
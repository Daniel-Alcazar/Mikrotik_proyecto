<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
</head>
<body>
{{-- Mostrar notificaciones --}}
@if(session('success'))
    <div class="notification success">
        <span>{{ session('success') }}</span>
    </div>
@elseif(session('error'))
    <div class="notification error">
        <span>{{ session('error') }}</span>
    </div>
@endif


<div class="sidebar">
    <a onclick="showSection('createUserGroup')">Crear Grupo de Usuario</a>
    <a onclick="showSection('user')">Agregar Usuario</a>
    <a onclick="showSection('bandwidth')">Ancho de Banda</a>
    <a onclick="showSection('newAddress')">New Address</a>
    <a onclick="showSection('ipRoute')">IP Route</a>
    <a onclick="showSection('dnsServer')">Agregar DNS Server</a>
    <a onclick="showSection('dhcp')">Configurar DHCP</a>
</div>

<div class="content">
    <div id="default" style="display: block;">
        <h2>Bienvenido al Dashboard</h2>
        <p>Selecciona una opción a la izquierda para empezar.</p>
    </div>

    <!-- Crear Grupo de Usuario -->
    <div id="createUserGroup" style="display: none;">
        <h2>Crear Grupo de Usuario</h2>
        <form method="POST" action="{{ route('create_user_group') }}">
            @csrf
            <input type="hidden" name="ip_address" value="{{ session('ip_address') }}">

            <label for="group_name">Nombre del Grupo:</label>
            <input type="text" id="group_name" name="group_name">

            <label for="policies">Políticas:</label>
            <input type="text" id="policies" name="policies">

            <button type="submit">Crear Grupo</button>
        </form>
    </div>

    <!-- Agregar Usuario -->
    <div id="user" style="display: none;">
        <h2>Agregar Usuario</h2>
        <form method="POST" action="{{ route('add_user') }}">
            @csrf
            <input type="hidden" name="ip_address" value="{{ session('ip_address') }}">

            <label for="username">Usuario:</label>
            <input type="text" id="username" name="username">

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password">

            <label for="profile">Perfil:</label>
            <input type="text" id="profile" name="profile">

            <button type="submit">Guardar Usuario</button>
        </form>
    </div>

    <!-- Configurar Ancho de Banda -->
    <div id="bandwidth" style="display: none;">
        <h2>Ancho de Banda</h2>
        <form method="POST" action="{{ route('set_bandwidth_limit') }}">
            @csrf
            <input type="hidden" name="ip_address" value="{{ session('ip_address') }}">

            <label for="target">Usuario:</label>
            <input type="text" id="target" name="target">

            <label for="download_limit">Límite de Descarga (Mbps):</label>
            <input type="text" id="download_limit" name="download_limit">

            <label for="upload_limit">Límite de Subida (Mbps):</label>
            <input type="text" id="upload_limit" name="upload_limit">

            <button type="submit">Guardar Ancho de Banda</button>
        </form>
    </div>

    <!-- Configurar New Address -->
    <div id="newAddress" style="display: none;">
        <h2>New Address</h2>
        <form method="POST" action="{{ route('add_new_address') }}">
            @csrf
            <input type="hidden" name="ip_address" value="{{ session('ip_address') }}">

            <label for="address">Dirección:</label>
            <input type="text" id="address" name="address">

            <label for="interface">Interfaz:</label>
            <input type="text" id="interface" name="interface">

            <button type="submit">Guardar Dirección</button>
        </form>
    </div>

    <!-- Configurar IP Route -->
    <div id="ipRoute" style="display: none;">
        <h2>IP Route</h2>
        <form method="POST" action="{{ route('add_ip_route') }}">
            @csrf
            <input type="hidden" name="ip_address" value="{{ session('ip_address') }}">
            
            <label for="gateway">Gateway:</label>
            <input type="text" id="gateway" name="gateway">

            <button type="submit">Guardar IP Route</button>
        </form>
    </div>

    <!-- Configurar DNS Server -->
    <div id="dnsServer" style="display: none;">
        <h2>Agregar DNS Server</h2>
        <form method="POST" action="{{ route('add_dns_servers') }}">
            @csrf
            <input type="hidden" name="ip_address" value="{{ session('ip_address') }}">

            <label for="servers">Servidores DNS:</label>
            <input type="text" id="servers" name="servers">

            <label for="remote_requests">Remote Requests:</label>
            <input type="text" id="remote_requests" name="remote_requests">

            <button type="submit">Guardar DNS Server</button>
        </form>
    </div>

    <!-- Configurar DHCP -->
    <div id="dhcp" style="display: none;">
        <h2>Configurar DHCP</h2>
        <form method="POST" action="{{ route('setup_dhcp') }}">
            @csrf
            <input type="hidden" name="ip_address" value="{{ session('ip_address') }}">

            <label for="dhcp_pool_name">Nombre del Pool DHCP:</label>
            <input type="text" id="dhcp_pool_name" name="dhcp_pool_name">

            <label for="pool_range">Rango de IP:</label>
            <input type="text" id="pool_range" name="pool_range">

            <label for="interface">Interfaz:</label>
            <input type="text" id="interface" name="interface">

            <label for="gateway">Gateway:</label>
            <input type="text" id="gateway" name="gateway">

            <label for="subnet_mask">Máscara de Subred:</label>
            <input type="text" id="subnet_mask" name="subnet_mask">

            <label for="dns_servers">Servidores DNS:</label>
            <input type="text" id="dns_servers" name="dns_servers">

            <button type="submit">Configurar DHCP</button>
        </form>
    </div>

</div>

<script>
    function showSection(section) {
        const sections = ["default", "createUserGroup", "user", "bandwidth", "newAddress", "ipRoute", "dnsServer", "dhcp"];
        sections.forEach(s => {
            document.getElementById(s).style.display = s === section ? 'block' : 'none';
        });
    }

    @if(Session::has('success'))
        toastr.success("{{ Session::get('success') }}");
    @elseif(Session::has('error'))
        toastr.error("{{ Session::get('error') }}");
    @endif
</script>

</body>
</html>
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\MikrotikOs;
use App\MyHelper\RouterosAPI;

class MikrotikController extends Controller
{
    public $API=[], $mikrotikos_data=[], $connection;
    
    public function test_api()
    {
        try{
            return response()->json([
                'success' => true,
                'message' => 'Bienvenido Mikrotik API'
            ]);
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetch data Mikrotik API'
            ]);
        }
    }

    public function store_mikrotikos($data)
    {
        $API = new RouterosAPI;
        $connection = $API->connect($data['ip_address'], $data['login'], $data['password']);

        if(!$connection) return response()->json(['error' => true, 'message' => 'Routeros no conectado ...'], 404);

        $store_mikrotikos_data = [
            'identity' => $API->comm('/system/identity/print')[0]['name'],
            'ip_address' => $data['ip_address'],
            'login' => $data['login'],
            'password' => $data['password'],
            'connect' => $connection  

        ];

        $store_mikrotikos = new MikrotikOs;
        $store_mikrotikos ->identity = $store_mikrotikos_data['identity'];
        $store_mikrotikos ->ip_address = $store_mikrotikos_data['ip_address'];
        $store_mikrotikos ->login = $store_mikrotikos_data['login'];
        $store_mikrotikos ->password =$store_mikrotikos_data['password'];
        $store_mikrotikos ->connect =$store_mikrotikos_data['connect'];
        $store_mikrotikos->save();

        return response()->json([
            'success' => true,
            'message' => 'Los datos de Routeros se han guardado en la base de datos dbmikrotik',
            'mikrotikos_data' => $store_mikrotikos
        ]);
    }

    public function mikrotikos_connection(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'login' => 'required',
                'password' => 'required'
            ]);
    
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
    
            $req_data = $request->only(['ip_address', 'login', 'password']);
            $mikrotikos_db = MikrotikOs::where('ip_address', $req_data['ip_address'])->first();
    
            if ($mikrotikos_db) {
                $API = new RouterosAPI;
                $connection = $API->connect($req_data['ip_address'], $req_data['login'], $req_data['password']);
    
                if ($connection) {
                    $mikrotikos_db->login = $req_data['login'];
                    $mikrotikos_db->password = $req_data['password'];
                    $mikrotikos_db->connect = $connection;
                    $mikrotikos_db->save();
    
                    // Guardar autenticación y grupos en la sesión
                    session(['authenticated' => true]);
                    session(['ip_address' => $req_data['ip_address']]);
    
                    // Obtener los grupos de usuario
                    $groups = $API->comm('/user/group/print');
                    session(['groups' => $groups]);

                    // Obtener y almacenar las interfaces de red
                    $interfaces = $API->comm('/interface/print');
                    session(['interfaces' => $interfaces]);
                    
                    return redirect()->route('show.Dashboard');
                } else {
                    return back()->with('error', 'Login o contraseña incorrectos');
                }
            } else {
                return $this->store_mikrotikos($req_data);
            }
        } catch (Exception $e) {
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function check_mikrotik_connection($data){

        $mikrotikos_db = MikrotikOs::where('ip_address', $data[
            'ip_address'])->get();

        if(count($mikrotikos_db) > 0):
            //echo $mikrotikos_db[0]['identity']; die;
            $API = new RouterosAPI;
            $connection = $API->connect($mikrotikos_db[0]['ip_address'],
            $mikrotikos_db[0]['login'], $mikrotikos_db[0]['password']);
            if(!$connection) return false;
            
            $this->API = $API;
            $this->connection = $connection;
            $this->mikrotikos_data = [
                'identity' => $this->API->comm('/system/identity/print')[0]['name'],
                'ip_address' => $mikrotikos_db[0]['ip_address'],
                'login' => $mikrotikos_db[0]['login'],
                'connect' => $connection
            ];
            return true;
        else:
            echo "Los datos de Routeros no son válidos en la base de datos, por favor cree la conexión de nuevo!";
        endif;
    }

    public function set_interface(Request $request){
        try{
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'id' => 'required',
                'interface' => 'required',
                'name' => 'required'
            ]);

            if($validator->fails()) return response()->json($validator->errors(), 404);
            
            if($this->check_mikrotik_connection($request->all())):
                $interface_lists = $this->API->comm('/interface/print');
                $find_interface = array_search($request->name, array_column($interface_lists, 'name'));
                
                if(!$find_interface):
                    $set_interface = $this->API->comm('/interface/set', [
                        '.id' => "*$request->id",
                        'name' => "$request->name"
                    ]);

                    return response()->json([
                        'success' => true,
                        'message' => "Se ha establecido correctamente la interfaz de : $request->interface, to : $request->name",
                        'interface_lists' => $this->API->comm('/interface/print')
                    ]);
                else:
                    return response()->json([
                        'success' => false,
                        'message' => "Nombre de la interfaz : $request->interface, with .id : *$request->id ya ha sido tomada de routeros",
                        'interface_lists' => $this->API->comm('/interface/print')
                    ]);
            endif;

        endif;
        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetch data Mikrotik API, '.$e->getMessage()
            ]);
        }
    }

    public function add_new_address(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'address' => 'required',
                'interface' => 'required'
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Error de validación: ' . implode(', ', $validator->errors()->all()));
            }
    
            if ($this->check_mikrotik_connection($request->all())) {
                $add_address = $this->API->comm('/ip/address/add', [
                    'address' => $request->address,
                    'interface' => $request->interface
                ]);
    
                $list_address = $this->API->comm('/ip/address/print');
                $find_address_id = array_search($add_address, array_column($list_address, '.id'));
    
                if (!$find_address_id) {
                    return redirect()->back()->with('error', 'Error: ' . $add_address['!trap'][0]['message']);
                }
    
                return redirect()->back()->with('success', "Añadida con éxito la nueva dirección en la interfaz: {$request->interface}.");
            } else {
                return redirect()->back()->with('error', 'Error de conexión con MikroTik.');
            }
    
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al obtener datos de la API de MikroTik: ' . $e->getMessage());
        }
    } 

    public function add_ip_route(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'gateway' => 'required'
            ]);
            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Error de validación: ' . implode(', ', $validator->errors()->all()));
            }
    
            if ($this->check_mikrotik_connection($request->all())) {
                $route_lists = $this->API->comm('/ip/route/print');
                $find_route_lists = array_search($request->gateway, array_column($route_lists, 'gateway'));
    
                if ($find_route_lists === 0) {
                    return redirect()->back()->with('error', "La dirección Gateway : {$request->gateway} ya está en uso.");
                } else {
                    $this->API->comm('/ip/route/add', [
                        'gateway' => $request->gateway
                    ]);
                    return redirect()->back()->with('success', "Nuevo Gateway {$request->gateway} añadido con éxito.");
                }
            } else {
                return redirect()->back()->with('error', 'Error de conexión con Mikrotik.');
            }
    
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al obtener datos de la API de RouterOS: ' . $e->getMessage());
        }
    }
    
    public function add_dns_servers(Request $request)
    {
        try {
            $schema = [
                'ip_address' => 'required',
                'servers' => 'required',
                'remote_requests' => 'required'
            ];
            $validator = Validator::make($request->all(), $schema);
    
            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Error de validación: ' . implode(', ', $validator->errors()->all()));
            }
    
            if ($this->check_mikrotik_connection($request->all())) {
                $add_dns = $this->API->comm('/ip/dns/set', [
                    'servers' => $request->servers,
                    'allow-remote-requests' => $request->remote_requests
                ]);
    
                if (count($add_dns) == 0) {
                    return redirect()->back()->with('success', 'Servidores DNS añadidos con éxito.');
                } else {
                    return redirect()->back()->with('error', 'Fallo al añadir los servidores DNS.');
                }
            } else {
                return redirect()->back()->with('error', 'Error de conexión con Mikrotik.');
            }
    
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al obtener datos de la API de Mikrotik: ' . $e->getMessage());
        }
    }
    

    public function routeros_reboot(Request $request)
    {
        try{
            $schema = [
                'ip_address' => 'required'
            ];

            $validator = Validator::make($request->all(), $schema);

            if($validator->fails()) return response()->json($validator->errors(), 404);

            if($this->check_mikrotik_connection($request->all())):
                $reboot = $this->API->comm('/system/reboot');

                return response()->json([
                    'reboot' => true,
                    'message' => 'Routeros has been reboot the system',
                    'connection' => $this->connection
                ]);

            endif;

        }catch(Exception $e){
            return response()->json([
                'success' => false,
                'message' => 'Error fetch data Mikrotik API, '.$e->getMessage()
            ]);
        }
    }

    public function add_user(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'username' => 'required',
                'password' => 'required',
                'profile' => 'required'
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
    
            if ($this->check_mikrotik_connection($request->all())) {
                // Obtener la lista de usuarios existentes
                $existing_users = $this->API->comm('/ip/hotspot/user/print');
    
                // Comprobar si el usuario ya existe
                $user_exists = array_search($request->username, array_column($existing_users, 'name'));
    
                if ($user_exists !== false) {
                    return redirect()->route('dashboard')->with('error', "El usuario con nombre {$request->username} ya existe en el servidor.");
                }
    
                // Si el usuario no existe, proceder a añadirlo
                $this->API->comm('/ip/hotspot/user/add', [
                    'name' => $request->username,
                    'password' => $request->password,
                    'profile' => $request->profile
                ]);
    
                return redirect()->route('show.Dashboard')->with('success', "Usuario {$request->username} agregado con éxito.");
            }
    
        } catch (Exception $e) {
            return redirect()->route('show.Dashboard')->with('error', 'Error al intentar agregar el usuario en MikroTik: ' . $e->getMessage());
        }
    }
    
    public function set_bandwidth_limit(Request $request)
    {
        try {
            // Validación de datos
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'target' => 'required',
                'download_limit' => 'required',
                'upload_limit' => 'required'
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()->with('error', 'Error de validación: ' . implode(', ', $validator->errors()->all()));
            }
    
            // Verificar conexión a MikroTik
            if ($this->check_mikrotik_connection($request->all())) {
                // Comprobar que la API está inicializada
                if (is_object($this->API)) {
                    // Configurar límite de ancho de banda
                    $this->API->comm('/queue/simple/add', [
                        'name' => $request->target,
                        'target' => $request->target,
                        'max-limit' => "{$request->upload_limit}/{$request->download_limit}"
                    ]);
    
                    return redirect()->back()->with('success', "Límite de ancho de banda establecido para {$request->target}.");
                } else {
                    return redirect()->back()->with('error', 'La API no está inicializada correctamente.');
                }
            } else {
                return redirect()->back()->with('error', 'Error de conexión con MikroTik.');
            }
    
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Error al establecer límite de ancho de banda: ' . $e->getMessage());
        }
    }
    

    public function create_user_group(Request $request)
    {
        try {
            // Validación de datos de entrada
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'group_name' => 'required',
                'policies' => 'required'  // Ej: "ssh,ftp,winbox,read"
            ]);
    
            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator)->withInput();
            }
    
            // Verificar conexión a MikroTik
            if ($this->check_mikrotik_connection($request->all())) {
                // Comprobar que la API está inicializada
                if (is_object($this->API)) {
                    // Verificar si el grupo ya existe
                    $existingGroup = $this->API->comm('/user/group/print', [
                        '?name' => $request->group_name
                    ]);
    
                    if (!empty($existingGroup)) {
                        return redirect()->route('dashboard')->with('error', "El grupo de usuario '{$request->group_name}' ya existe.");
                    }
    
                    // Crear grupo de usuario
                    $this->API->comm('/user/group/add', [
                        'name' => $request->group_name,
                        'policy' => $request->policies
                    ]);
    
                    return redirect()->route('show.Dashboard')->with('success', "Grupo de usuario '{$request->group_name}' creado con éxito.");
                } else {
                    return redirect()->route('show.Dashboard')->with('error', 'La API no está inicializada correctamente.');
                }
            } else {
                return redirect()->route('show.Dashboard')->with('error', 'No se pudo conectar a RouterOS. Verifique los datos de conexión.');
            }
        } catch (Exception $e) {
            return redirect()->route('show.Dashboard')->with('error', 'Error al crear grupo en RouterOS: ' . $e->getMessage());
        }
    }

    public function showDashboard()
    {
        try {
            // Verificar la conexión a MikroTik
            if ($this->check_mikrotik_connection(session('ip_address'))) {
                // Obtener lista de usuarios desde MikroTik
                $user_list = $this->API->comm('/ip/hotspot/user/print');
                
                // Depuración: Imprimir lo que devuelve la API
                Log::debug('Usuarios obtenidos:', $user_list);
    
                // Verificar si la respuesta está vacía
                if (empty($user_list)) {
                    throw new Exception('No se encontraron usuarios.');
                }
            } else {
                $user_list = [];
            }
    
            return view('dashboard', compact('user_list'));
        } catch (Exception $e) {
            Log::error('Error al cargar usuarios: ' . $e->getMessage());
            return view('dashboard')->with('error', 'Error al cargar usuarios: ' . $e->getMessage());
        }
    }
    
    public function setup_dhcp(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'ip_address' => 'required',
                'dhcp_pool_name' => 'required',
                'pool_range' => 'required',
                'interface' => 'required',
                'gateway' => 'required',
                'subnet_mask' => 'required',
                'dns_servers' => 'required'
            ]);
    
            if ($validator->fails()) {
                return redirect()->route('show.Dashboard')->withErrors($validator);
            }
    
            if ($this->check_mikrotik_connection($request->all())) {
                // 1. Crear el DHCP Pool
                $this->API->comm('/ip/pool/add', [
                    'name' => $request->dhcp_pool_name,
                    'ranges' => $request->pool_range
                ]);
    
                // 2. Configurar la red DHCP
                $this->API->comm('/ip/dhcp-server/network/add', [
                    'address' => "{$request->gateway}/24",
                    'netmask' => $request->subnet_mask,
                    'gateway' => $request->gateway,
                    'dns-server' => $request->dns_servers,
                ]);
    
                // 3. Activar el servidor DHCP
                $this->API->comm('/ip/dhcp-server/add', [
                    'name' => 'dhcp1',
                    'interface' => $request->interface,
                    'address-pool' => $request->dhcp_pool_name,
                    'disabled' => 'no'
                ]);
    
                // Redirigir al dashboard con mensaje de éxito
                return redirect()->route('show.Dashboard')->with('success', 'Configuración de DHCP completada exitosamente.');
            }
    
        } catch (Exception $e) {
            // Redirigir al dashboard con mensaje de error
            return redirect()->route('show.Dashboard')->with('error', 'Error al configurar el DHCP en Mikrotik API: ' . $e->getMessage());
        }
    }  
}
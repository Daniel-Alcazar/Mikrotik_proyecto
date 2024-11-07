<?php
namespace App\Http\Controllers\Api;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get('/dashboard', [FrontController::class, 'showDashboard'])->name('show.Dashboard');

Route::get('/', [FrontController::class, 'showLogin'])->name('login.show');
Route::post('/login', [FrontController::class, 'authenticate'])->name('login.submit');


Route::prefix('v1')->group(function(){
    Route::post('/test-api', [MikrotikController::class, 'test_api']);

    Route::post('/mikrotikos-connect', [MikrotikController::class, 'mikrotikos_connection'])->name('router.login');

    Route::post('/set-interface', [MikrotikController::class, 'set_interface']);

    Route::post('/add-new-address', [MikrotikController::class, 'add_new_address']);

    Route::post('/add-ip-route', [MikrotikController::class, 'add_ip_route'])->name('add_ip_route');

    Route::post('/add-dns-server', [MikrotikController::class, 'add_dns_servers']);

    Route::post('/routeros-reboot', [MikrotikController::class, 'routeros_reboot']);

    Route::post('/add-user', [MikrotikController::class, 'add_user']);

    Route::post('/set-bandwidth-limit', [MikrotikController::class, 'set_bandwidth_limit']);

    Route::post('/create-user-group', [MikrotikController::class, 'create_user_group']);
});
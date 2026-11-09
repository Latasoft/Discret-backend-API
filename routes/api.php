<?php

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\EnvioProgramadoController;
use App\Http\Controllers\DashboardMetricController;
use App\Http\Controllers\HabitacionController;
use App\Http\Controllers\OnboardingController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\SuscripcionController;
use App\Http\Controllers\TipoHabitacionController;
use App\Http\Controllers\ValoracionController;
use App\Models\ConfiguracionSistema;
use App\Models\ApiToken;
use App\Models\User;
use App\Support\CurrentMotel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/ping', fn () => [
    'app' => 'DISCRET API',
    'status' => 'ok',
]);

Route::post('/webhooks/mercado-pago', [SuscripcionController::class, 'webhook']);
Route::post('/onboarding/propietario', [OnboardingController::class, 'store']);

Route::post('/admin/login', function (Request $request) {
    $data = $request->validate([
        'email' => ['required', 'email'],
        'password' => ['required', 'string'],
    ]);

    $user = User::where('email', $data['email'])->first();

    if (! $user || ! Hash::check($data['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Las credenciales de administrador no son correctas.'],
        ]);
    }

    $role = $user->role ?: (strtolower($user->email) === 'admin@discret.cl' ? 'super_admin' : 'admin');

    return response()->json([
        'token' => ApiToken::issue('admin', $user->id, $user->id_motel),
        'admin' => [
            'id' => $user->id,
            'id_motel' => $user->id_motel,
            'name' => $user->name,
            'email' => $user->email,
            'is_super_admin' => $role === 'super_admin',
            'role' => $role,
        ],
    ]);
});

Route::post('/clientes/login', [ClienteController::class, 'login']);
Route::post('/clientes/recuperar-password', [ClienteController::class, 'solicitarRecuperacion']);
Route::post('/clientes/restablecer-password', [ClienteController::class, 'restablecerPassword']);
Route::post('/clientes', [ClienteController::class, 'store']);
Route::get('/tipos-habitacion', [TipoHabitacionController::class, 'index'])->middleware('api.actor:optional');
Route::get('/habitaciones', [HabitacionController::class, 'index'])->middleware('api.actor:optional');
Route::post('/reservas', [ReservaController::class, 'store']);
Route::get('/valoraciones/{token}', [ValoracionController::class, 'showByToken']);
Route::post('/valoraciones/{token}', [ValoracionController::class, 'storeByToken']);

Route::middleware('api.actor:client')->group(function () {
    Route::get('/mi/reservas', [ReservaController::class, 'mine']);
    Route::post('/mi/reservas/{reserva}/enviar-comprobante', [ReservaController::class, 'enviarComprobante']);
});

Route::post('/auth/logout', function (Request $request) {
    ApiToken::where('token_hash', hash('sha256', $request->bearerToken()))->delete();

    return response()->noContent();
})->middleware('api.actor:any');

Route::middleware('api.actor:admin')->group(function () {
    Route::get('/dashboard/metricas', [DashboardMetricController::class, 'index']);
    Route::get('/suscripcion/estado', [SuscripcionController::class, 'estado']);
    Route::post('/suscripcion/iniciar', [SuscripcionController::class, 'iniciar']);

    Route::apiResource('clientes', ClienteController::class)->except(['store']);
    Route::apiResource('tipos-habitacion', TipoHabitacionController::class)
        ->except(['index'])
        ->parameters(['tipos-habitacion' => 'tipoHabitacion']);
    Route::apiResource('habitaciones', HabitacionController::class)
        ->except(['index'])
        ->parameters(['habitaciones' => 'habitacion']);
    Route::post('/reservas/{reserva}/enviar-comprobante', [ReservaController::class, 'enviarComprobante']);
    Route::apiResource('reservas', ReservaController::class)->except(['store']);
    Route::apiResource('reportes', ReporteController::class)
        ->only(['index', 'store', 'show', 'destroy'])
        ->parameters(['reportes' => 'reporte']);
    Route::get('/envios-programados', [EnvioProgramadoController::class, 'index']);
    Route::post('/envios-programados/{envioProgramado}/reenviar-valoracion', [EnvioProgramadoController::class, 'reenviarValoracion']);
    Route::get('/valoraciones', [ValoracionController::class, 'index']);

    Route::get('/configuracion', fn (Request $request) => ConfiguracionSistema::where('id_motel', CurrentMotel::id($request))
        ->orderBy('clave')
        ->get());

    Route::put('/configuracion/{clave}', function (Request $request, string $clave) {
        $data = $request->validate([
            'valor' => ['required', 'array'],
            'descripcion' => ['nullable', 'string', 'max:200'],
        ]);

        $idMotel = CurrentMotel::id($request);

        return ConfiguracionSistema::updateOrCreate(
            [
                'id_motel' => $idMotel,
                'clave' => $clave,
            ],
            $data,
        );
    });
});


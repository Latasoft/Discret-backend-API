<?php

namespace App\Http\Controllers;

use App\Mail\ClientePasswordResetMail;
use App\Models\Cliente;
use App\Models\ApiToken;
use App\Support\CurrentMotel;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rule;

class ClienteController extends Controller
{
    public function index(Request $request)
    {
        return Cliente::where('id_motel', CurrentMotel::id($request))
            ->latest()
            ->paginate(20);
    }

    public function store(Request $request)
    {
        $idMotel = CurrentMotel::id($request);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'apellido' => ['required', 'string', 'max:120'],
            'telefono' => ['required', 'string', 'max:40'],
            'correo' => [
                'required',
                'email',
                'max:160',
                Rule::unique('clientes', 'correo')->where('id_motel', $idMotel),
            ],
            'fecha_nacimiento' => ['required', 'date'],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'mayor_edad_confirmado' => ['accepted'],
        ]);

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $data['id_motel'] = $idMotel;

        return response()->json(Cliente::create($data), 201);
    }

    public function login(Request $request)
    {
        $idMotel = CurrentMotel::id($request);
        $data = $request->validate([
            'correo' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $cliente = Cliente::where('correo', $data['correo'])
            ->where('id_motel', $idMotel)
            ->where('activo', true)
            ->first();

        if (! $cliente || ! $cliente->password || ! Hash::check($data['password'], $cliente->password)) {
            throw ValidationException::withMessages([
                'correo' => ['Las credenciales no son correctas.'],
            ]);
        }

        $cliente->update(['ultimo_acceso_at' => now()]);

        return response()->json([
            'token' => ApiToken::issue('client', $cliente->id, $cliente->id_motel),
            'cliente' => $cliente->fresh(),
        ]);
    }

    public function solicitarRecuperacion(Request $request)
    {
        $idMotel = CurrentMotel::id($request);
        $data = $request->validate([
            'correo' => ['required', 'email'],
        ]);

        $cliente = Cliente::where('correo', $data['correo'])
            ->where('id_motel', $idMotel)
            ->where('activo', true)
            ->first();

        if ($cliente) {
            $token = Str::random(64);

            DB::table('client_password_reset_tokens')->updateOrInsert(
                [
                    'id_motel' => $idMotel,
                    'email' => $cliente->correo,
                ],
                [
                    'token' => Hash::make($token),
                    'created_at' => now(),
                ],
            );

            $frontendUrl = rtrim(config('app.frontend_url'), '/');
            $resetUrl = $frontendUrl.'/restablecer-contrasena?'.http_build_query([
                'correo' => $cliente->correo,
                'token' => $token,
            ]);

            try {
                Mail::to($cliente->correo)->send(new ClientePasswordResetMail($cliente, $resetUrl));
            } catch (\Throwable $exception) {
                Log::warning('No se pudo enviar el correo de recuperacion de contraseña del cliente.', [
                    'cliente_id' => $cliente->id,
                    'correo' => $cliente->correo,
                    'error' => $exception->getMessage(),
                ]);
            }
        }

        return response()->json([
            'message' => 'Si el correo existe en el sistema, recibirás las instrucciones para restablecer tu acceso.',
        ]);
    }

    public function restablecerPassword(Request $request)
    {
        $idMotel = CurrentMotel::id($request);
        $data = $request->validate([
            'correo' => ['required', 'email'],
            'token' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $reset = DB::table('client_password_reset_tokens')
            ->where('id_motel', $idMotel)
            ->where('email', $data['correo'])
            ->first();

        if (
            ! $reset
            || ! Hash::check($data['token'], $reset->token)
            || now()->diffInMinutes($reset->created_at) > 60
        ) {
            throw ValidationException::withMessages([
                'correo' => ['El enlace de recuperación no es válido o ya expiró.'],
            ]);
        }

        $cliente = Cliente::where('correo', $data['correo'])
            ->where('id_motel', $idMotel)
            ->where('activo', true)
            ->firstOrFail();

        $cliente->update([
            'password' => Hash::make($data['password']),
        ]);

        DB::table('client_password_reset_tokens')
            ->where('id_motel', $idMotel)
            ->where('email', $cliente->correo)
            ->delete();

        ApiToken::where('actor_type', 'client')
            ->where('actor_id', $cliente->id)
            ->delete();

        return response()->json([
            'message' => 'Tu contraseña fue actualizada correctamente.',
        ]);
    }

    public function show(Cliente $cliente)
    {
        return $cliente->load('reservas');
    }

    public function update(Request $request, Cliente $cliente)
    {
        $data = $request->validate([
            'nombre' => ['sometimes', 'required', 'string', 'max:120'],
            'apellido' => ['sometimes', 'required', 'string', 'max:120'],
            'telefono' => ['sometimes', 'required', 'string', 'max:40'],
            'correo' => [
                'sometimes',
                'required',
                'email',
                'max:160',
                Rule::unique('clientes', 'correo')
                    ->where('id_motel', $cliente->id_motel)
                    ->ignore($cliente),
            ],
            'fecha_nacimiento' => ['sometimes', 'required', 'date'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $cliente->update($data);

        return $cliente;
    }

    public function destroy(Cliente $cliente)
    {
        $cliente->update(['activo' => false]);

        return response()->noContent();
    }
}

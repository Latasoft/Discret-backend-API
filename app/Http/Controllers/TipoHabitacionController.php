<?php

namespace App\Http\Controllers;

use App\Models\TipoHabitacion;
use App\Support\CurrentMotel;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TipoHabitacionController extends Controller
{
    public function index(Request $request)
    {
        return TipoHabitacion::where('id_motel', CurrentMotel::id($request))
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    public function store(Request $request)
    {
        $idMotel = CurrentMotel::id($request);

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120', Rule::unique('tipo_habitaciones', 'nombre')->where('id_motel', $idMotel)],
            'descripcion' => ['nullable', 'string'],
            'precio_base' => ['required', 'integer', 'min:0'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $data['id_motel'] = $idMotel;

        return response()->json(TipoHabitacion::create($data), 201);
    }

    public function show(TipoHabitacion $tipoHabitacion)
    {
        return $tipoHabitacion->load('habitaciones');
    }

    public function update(Request $request, TipoHabitacion $tipoHabitacion)
    {
        $idMotel = CurrentMotel::id($request);
        $data = $request->validate([
            'nombre' => [
                'sometimes',
                'required',
                'string',
                'max:120',
                Rule::unique('tipo_habitaciones', 'nombre')->where('id_motel', $idMotel)->ignore($tipoHabitacion),
            ],
            'descripcion' => ['nullable', 'string'],
            'precio_base' => ['sometimes', 'required', 'integer', 'min:0'],
            'activo' => ['sometimes', 'boolean'],
        ]);

        $tipoHabitacion->update($data);

        return $tipoHabitacion;
    }

    public function destroy(TipoHabitacion $tipoHabitacion)
    {
        $tipoHabitacion->update(['activo' => false]);

        return response()->noContent();
    }
}

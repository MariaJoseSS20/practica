<?php

namespace App\Http\Controllers;

use App\Models\TituloTipo;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TituloTipoController extends Controller
{
    // Definir las reglas de validación para los datos de entrada
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:150',
        ];
    }

    // Método para obtener todos los tipos de título
    public function index(Request $request)
    {
        // Obtener todos los registros de la tabla titulos_tipos
        $titulosTipos = TituloTipo::all();

        // Si la petición es AJAX, retornar la respuesta JSON
        if ($request->expectsJson()) {
            return response()->json($titulosTipos);
        }

        // Si no es una petición AJAX, devolver la vista con Inertia
        return Inertia::render('TitulosTipos/Index', [
            'titulosTipos' => $titulosTipos,
        ]);
    }

    // Método para almacenar un nuevo tipo de título
    public function store(Request $request)
    {
        // Validar los datos de entrada
        $validated = $request->validate($this->rules());
        
        try {
            // Crear el nuevo tipo de título
            $tituloTipo = TituloTipo::create($validated);

            // Si la petición es AJAX, responder con JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Título creado correctamente',
                    'tituloTipo' => $tituloTipo
                ]);
            }

            // Si no es una petición AJAX, redirigir con Inertia
            return Inertia::render('TitulosTipos/Index', [
                'titulosTipos' => TituloTipo::latest()->get(),
            ])->with('success', 'Título creado correctamente');
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json(['error' => 'Error al guardar el título'], 500);
        }
    }

    // Método para actualizar un tipo de título
    public function update(Request $request, $id)
    {
        try {
            // Buscar el título por su ID
            $tituloTipo = TituloTipo::findOrFail($id);

            // Validar los datos de entrada
            $validated = $request->validate($this->rules());

            // Actualizar el registro
            $tituloTipo->update($validated);

            // Si la petición es AJAX, responder con JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Título actualizado correctamente',
                    'tituloTipo' => $tituloTipo
                ]);
            }

            // Si no es una petición AJAX, redirigir con Inertia
            return Inertia::render('TitulosTipos/Index', [
                'titulosTipos' => TituloTipo::latest()->get(),
            ])->with('success', 'Título actualizado correctamente');
        } catch (\Exception $e) {
            // Manejo de errores
            return response()->json(['error' => 'Error al actualizar el título'], 500);
        }
    }

    // Método para eliminar un tipo de título
    public function destroy(Request $request, $id)
    {
        // Buscar el título por su ID
        $tituloTipo = TituloTipo::findOrFail($id);

        try {
            // Eliminar el título
            $tituloTipo->delete();

            // Si la petición es AJAX, responder con JSON
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Título eliminado correctamente']);
            }

            // Si no es una petición AJAX, redirigir con Inertia
            return Inertia::render('TitulosTipos/Index', [
                'titulosTipos' => TituloTipo::latest()->get(),
            ])->with('success', 'Título eliminado correctamente');
        } catch (\Exception $e) {
            // En caso de error, responder con JSON
            return response()->json(['error' => 'Error al eliminar el título'], 500);
        }
    }
}

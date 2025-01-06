<?php

use App\Http\Controllers\TituloTipoController;  // Asegúrate de importar el controlador
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Models\TituloTipo;

// Ruta principal (home) que se usará para Inertia
Route::get('/', function () {
    return Inertia::render('TitulosTipos/Index', [
        'titulosTipos' => TituloTipo::latest()->get(),  // Pasar los datos a Vue
    ]);
})->name('home'); // Asegúrate de que esta ruta tenga el nombre 'home'


// Ruta para obtener todos los tipos de título y mostrarlos en una vista con Inertia
Route::get('/titulos-tipos', [TituloTipoController::class, 'index'])->name('titulos-tipos.index');

// Ruta para almacenar un nuevo tipo de título
Route::post('/titulos-tipos', [TituloTipoController::class, 'store'])->name('titulos-tipos.store');

// Ruta para actualizar un tipo de título
Route::put('/titulos-tipos/{id}', [TituloTipoController::class, 'update'])->name('titulos-tipos.update');

// Ruta para eliminar un tipo de título
Route::delete('/titulos-tipos/{id}', [TituloTipoController::class, 'destroy'])->name('titulos-tipos.destroy');

<?php

use App\Http\Controllers\EstudianteController;
use App\Http\Controllers\SesionController;
use Illuminate\Support\Facades\Route;

// La página inicial lleva al listado (si no hay sesión, el middleware auth manda al login).
Route::redirect('/', '/estudiantes');

// Solo para visitantes sin sesión.
Route::middleware('guest')->group(function () {
    Route::get('/login', [SesionController::class, 'create'])->name('login');
    Route::post('/login', [SesionController::class, 'store'])->middleware('throttle:5,1');
});

// Páginas protegidas: requieren sesión iniciada.
Route::middleware('auth')->group(function () {
    Route::post('/logout', [SesionController::class, 'destroy'])->name('logout');
    Route::resource('estudiantes', EstudianteController::class)->except('show');
});

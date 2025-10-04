<?php

use App\Http\Controllers\Auth\AuthController;

use App\Http\Controllers\Auth\ResetPassword\PasswordResetController;
use App\Http\Controllers\Cliente\ClienteController;
use App\Http\Controllers\Empleado\EmpleadoController;
use App\Http\Controllers\Pago\PagoController;
use App\Http\Controllers\Prestamo\PrestamoController;
use App\Http\Controllers\Producto\ProductoController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/refresh', [AuthController::class, 'refresh']);

Route::post('/validate-refresh-token', [AuthController::class, 'validateRefreshToken']);

Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword']);

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:admin'])->group(function () { 

    //RUTAS CLIENTES
    Route::post('/cliente/store', [ClienteController::class, 'store']);
    Route::get('/clientes/index', [ClienteController::class, 'index']);
    Route::put('/cliente/update/{cliente}', [ClienteController::class, 'update']);
    Route::patch('/cliente/cambiar-estado/{cliente}', [ClienteController::class, 'toggleEstado']);

    //RUTAS PRODUCTO
    Route::get('/productos/index', [ProductoController::class, 'index']);
    Route::post('/producto/store', [ProductoController::class, 'store']);
    Route::put('/producto/update/{producto}', [ProductoController::class, 'update']); 

    //RUTAS EMPLEADO
    Route::get('/empleados/index', [EmpleadoController::class, 'index']);
    Route::post('/empleado/store', [EmpleadoController::class, 'store']);
    Route::get('/empleado/show/{empleado}', [EmpleadoController::class, 'show']);
    Route::put('/empleado/update/{empleado}', [EmpleadoController::class, 'update']);
    Route::patch('/empleado/toggleEstado/{empleado}', [EmpleadoController::class, 'toggleEstado']);

    //RUTAS PRESTAMO
    Route::post('/prestamo/store', [PrestamoController::class, 'store']);
    Route::post('/prestamo/extornar/{prestamo}', [PrestamoController::class, 'destroy']);
    Route::put('/prestamo/update/{prestamo}', [PrestamoController::class, 'update']);
});

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:cliente'])->group(function () { 


});

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:asesor'])->group(function () { 



});

// RUTAS PARA cliente VALIDADA POR MIDDLEWARE AUTH (PARA TOKEN JWT) Y CHECKROLE (PARA VALIDAR ROL DEL TOKEN)
Route::middleware(['auth.jwt', 'checkRoleMW:cajero'])->group(function () { 

    //RUTAS CRUD PAGOS
    Route::post('/pago/store', [PagoController::class, 'store']);
    Route::post('/pago/cancelar-total', [PagoController::class, 'cancelarTotal']);

});


// RUTAS PARA ROL ADMIN Y CAJERO
Route::middleware(['auth.jwt', 'CheckRolesMW_ADMIN_CAJERO'])->group(function () { 
    
    //RUTAS CLIENTES
    Route::get('/cliente/show/{cliente}', [ClienteController::class, 'show']);

    //RUTAS PRESTAMOS
    Route::get('/prestamos/index', [PrestamoController::class, 'index']);
    Route::get('/prestamo/show/{prestamo}', [PrestamoController::class, 'show']);
});

// RUTAS PARA VARIOS ROLES
Route::middleware(['auth.jwt', 'checkRolesMW'])->group(function () { 

    Route::post('/logout', [AuthController::class, 'logout']);

});


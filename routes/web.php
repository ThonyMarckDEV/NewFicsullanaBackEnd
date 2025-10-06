<?php



use App\Http\Controllers\Auth\ResetPassword\PasswordResetController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/reset-password/{id_Usuario}/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset.form');
Route::post('/reset-password/{id_Usuario}/{token}', [PasswordResetController::class, 'reset'])->name('password.reset.submit');

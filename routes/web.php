<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PlanController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes();

Route::middleware('auth')->group(function () {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    // User
    Route::group(['prefix' => 'user'], function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('create', [UserController::class, 'create']);
        Route::post('store', [UserController::class, 'store']);
        Route::get('edit/{id}', [UserController::class, 'edit']);
        Route::get('destroy/{id}', [UserController::class, 'destroy']);
        Route::get('change-password', [UserController::class, 'changePassword']);
        Route::post('update-password', [UserController::class, 'updatePassword']);
    });

    // Invoice
    Route::group(['prefix' => 'invoice'], function () {
        Route::get('/', [InvoiceController::class, 'index']);
        Route::get('create', [InvoiceController::class, 'create']);
        Route::get('view/{id}', [InvoiceController::class, 'view']);
        Route::post('store', [InvoiceController::class, 'store']);
        Route::get('destroy/{id}', [InvoiceController::class, 'destroy']);
    });
});

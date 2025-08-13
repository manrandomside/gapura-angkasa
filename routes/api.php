<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->group(function () {
    // Unit Organisasi API Routes
    Route::get('/units/by-organisasi', [EmployeeController::class, 'getUnits'])->name('api.units.by-organisasi');
    Route::get('/units', [EmployeeController::class, 'getUnits'])->name('api.units');
    
    // Sub Unit API Routes
    Route::get('/sub-units/by-unit', [EmployeeController::class, 'getSubUnits'])->name('api.sub-units.by-unit');
    Route::get('/sub-units', [EmployeeController::class, 'getSubUnits'])->name('api.sub-units');
    
    // Employee validation routes
    Route::post('/employees/validate-nik', [EmployeeController::class, 'validateNik'])->name('api.employees.validate-nik');
    Route::post('/employees/validate-nip', [EmployeeController::class, 'validateNip'])->name('api.employees.validate-nip');
    
    // Get user info
    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

// Fallback routes untuk backward compatibility
Route::get('/api/units/by-organisasi', [EmployeeController::class, 'getUnits']);
Route::get('/api/sub-units/by-unit', [EmployeeController::class, 'getSubUnits']);
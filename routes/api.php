<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\MenuController;
use App\Http\Controllers\Api\QuotationController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ComplaintController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Menu
    Route::get('/menu-items', [MenuController::class, 'index']);
    Route::get('/categories', [MenuController::class, 'categories']);
    
    // Quotations
    Route::post('/quotations', [QuotationController::class, 'store']);
    Route::get('/quotations/{id}', [QuotationController::class, 'show']);
    
    // Orders
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    
    // Invoices
    Route::get('/invoices/{id}', [InvoiceController::class, 'show']);
    Route::get('/invoices/{id}/download', [InvoiceController::class, 'download']);
    
    // Payments
    Route::post('/payments', [PaymentController::class, 'store']);
});

// Public complaint endpoint
Route::post('/complaints', [ComplaintController::class, 'store']);

// Admin complaint endpoint
Route::middleware('auth:sanctum')->get('/complaints', [ComplaintController::class, 'index']);

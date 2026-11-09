<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\ReportAiController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\ShowcaseController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\ThreadController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register'])->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::get('/showcase', [ShowcaseController::class, 'index']);
Route::get('/showcase/{product}', [ShowcaseController::class, 'show']);
Route::get('/articles', [ArticleController::class, 'index']);
Route::get('/articles/{article}', [ArticleController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardController::class, 'index']);
    Route::get('/business', [BusinessController::class, 'show']);
    Route::post('/business', [BusinessController::class, 'store']);

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/product-images', [ProductImageController::class, 'index']);
    Route::post('/products', [ProductController::class, 'store']);
    Route::get('/products/{product}', [ProductController::class, 'show']);
    Route::put('/products/{product}', [ProductController::class, 'update']);
    Route::post('/products/{product}/showcase', [ProductController::class, 'showcase']);
    Route::delete('/products/{product}', [ProductController::class, 'destroy']);

    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']);
    Route::get('/customers/{customer}', [CustomerController::class, 'show']);

    Route::get('/sales', [SaleController::class, 'index']);
    Route::post('/sales', [SaleController::class, 'store']);
    Route::get('/sales/{sale}', [SaleController::class, 'show']);

    Route::get('/stock', [StockController::class, 'index']);
    Route::get('/stock/movements', [StockController::class, 'movements']);
    Route::post('/stock/adjust', [StockController::class, 'adjust']);

    Route::get('/purchases', [PurchaseController::class, 'index']);
    Route::get('/purchases/suppliers', [PurchaseController::class, 'suppliers']);
    Route::post('/purchases', [PurchaseController::class, 'store']);
    Route::post('/purchases/{purchase}/receive', [PurchaseController::class, 'receive']);

    Route::get('/expenses', [ExpenseController::class, 'index']);
    Route::post('/expenses', [ExpenseController::class, 'store']);

    Route::get('/leads', [LeadController::class, 'index']);
    Route::get('/leads/follow-ups', [LeadController::class, 'followUps']);
    Route::post('/leads', [LeadController::class, 'store']);
    Route::patch('/leads/{lead}', [LeadController::class, 'update']);

    Route::get('/reports/summary', [ReportController::class, 'summary']);
    Route::post('/reports/ai/chat', [ReportAiController::class, 'chat']);

    Route::get('/notifications', [NotificationController::class, 'index']);

    Route::get('/shops', [ShopController::class, 'index']);

    Route::get('/threads', [ThreadController::class, 'index']);
    Route::post('/threads', [ThreadController::class, 'store']);
    Route::get('/threads/{thread}/messages', [ThreadController::class, 'messages']);
    Route::post('/threads/{thread}/messages', [ThreadController::class, 'reply']);
});

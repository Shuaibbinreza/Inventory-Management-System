<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OAuth\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\ExportController;

Route::get('/', function () {
    return redirect()->route('inventory.dashboard');
});

// OAuth Routes
Route::get('/oauth/authorize', [AuthController::class, 'authorize']);
Route::post('/oauth/token', [AuthController::class, 'token']);

// Inventory Management Routes
Route::prefix('inventory')->name('inventory.')->group(function () {
    // Dashboard
    Route::get('/dashboard', [InventoryController::class, 'dashboard'])->name('dashboard');
    
    // Products
    Route::get('/products', [InventoryController::class, 'products'])->name('products');
    Route::get('/products/create', [InventoryController::class, 'createProduct'])->name('create-product');
    Route::post('/products', [InventoryController::class, 'storeProduct'])->name('store-product');
    
    // Sales
    Route::get('/sales', [InventoryController::class, 'sales'])->name('sales');
    Route::get('/sales/create', [InventoryController::class, 'createSale'])->name('create-sale');
    Route::post('/sales', [InventoryController::class, 'storeSale'])->name('store-sale');
    Route::get('/sales/export', [ExportController::class, 'sales'])->name('export-sales');
    
    // Journal Entries
    Route::get('/journal-entries', [InventoryController::class, 'journalEntries'])->name('journal-entries');
    Route::get('/journal-entries/export', [ExportController::class, 'journalEntries'])->name('export-journal-entries');
    
    // Financial Report
    Route::get('/financial-report', [InventoryController::class, 'financialReport'])->name('financial-report');
    Route::get('/financial-report/export', [ExportController::class, 'financialReport'])->name('export-financial-report');
    Route::get('/expense/create', [InventoryController::class, 'createExpense'])->name('create-expense');
    Route::post('/expense', [InventoryController::class, 'storeExpense'])->name('store-expense');
    
    // Demo Data
    Route::get('/init-demo', [InventoryController::class, 'initDemoData'])->name('init-demo');
});

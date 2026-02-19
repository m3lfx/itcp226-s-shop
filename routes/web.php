<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Auth;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/', [ItemController::class, 'getItems'])->name('getItems');
Route::get('/add-to-cart/{id}', [ItemController::class, 'addToCart'])->name('addToCart');
Route::get('/shopping-cart', [ItemController::class, 'getCart'])->name('getCart');
Route::get('/reduce/{id}', [ItemController::class, 'getReduceByOne'])->name('reduceByOne');
Route::get('/remove/{id}', [ItemController::class, 'getRemoveItem'])->name('removeItem');



Route::post('/items-import', [ItemController::class, 'import'])->name('item.import');
Route::prefix('admin')->group(function () {
    Route::get('/users', [DashboardController::class, 'getUsers'])->name('admin.users');
    Route::get('/customers', [DashboardController::class, 'getCustomers'])->name('admin.customers');
    Route::get('/orders', [DashboardController::class, 'getOrders'])->name('admin.orders');
    Route::get('/order/{id}', [OrderController::class, 'processOrder'])->name('admin.orderDetails');
    Route::post('/order/{id}', [OrderController::class, 'orderUpdate'])->name('admin.orderUpdate');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
});


Route::middleware(['auth'])->group(function () {
    Route::get('/checkout', [ItemController::class, 'postCheckout'])->name('checkout');
    Route::get('/user/logout', [CustomerController::class, 'logout'])->name('user.logout');
});
Auth::routes();
Route::resource('customers', CustomerController::class);
Route::resource('items', ItemController::class);
Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
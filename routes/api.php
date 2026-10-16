<?php

use App\Http\Controllers\API\ProduitController;
use App\Http\Controllers\API\CertificationController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('/search-product', [ProduitController::class, 'search']);

// Certification API
Route::post('/v1/certifier', [CertificationController::class, 'certifier']);

// User activity history API
Route::get('/users/{id}/activities', [UserController::class, 'getActivityHistory']);
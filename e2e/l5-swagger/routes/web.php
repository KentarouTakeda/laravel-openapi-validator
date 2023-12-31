<?php

use App\Http\Controllers\ExampleController;
use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware\ValidateRequestResponse;

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

Route::get('/', ExampleController::class)
    ->middleware(ValidateRequestResponse::class);

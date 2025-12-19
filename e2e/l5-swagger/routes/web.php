<?php

use App\Http\Controllers\ExampleController;
use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Middleware\OpenApiValidator;

Route::get('/', ExampleController::class)
    ->middleware(OpenApiValidator::class);

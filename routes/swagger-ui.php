<?php

use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Support\Facades\Route;
use KentarouTakeda\Laravel\OpenApiValidator\Config\Config;
use KentarouTakeda\Laravel\OpenApiValidator\Http\Controllers\DocumentController;

$config = app()->make(Config::class);
assert($config instanceof Config);

Route::prefix('openapi-validator/documents')->name('openapi-validator.')->group(function() use($config){

    Route::get('_assets/{path}', [DocumentController::class, 'asset'])->name('asset');

    foreach($config->getProviderNames() as $providerName) {
        Route::get($providerName, [DocumentController::class, 'view'])->name("document.{$providerName}");
    }

});

<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service' => 'vacancy-api',
        'frontend' => 'Запусти SPA из папки frontend (npm run dev) или укажи VITE_API_BASE_URL при сборке.',
    ], 200, [], JSON_UNESCAPED_UNICODE);
});

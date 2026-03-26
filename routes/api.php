<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\CityController;
use App\Http\Controllers\VacancyController;
use Illuminate\Support\Facades\Route;

Route::get('/areas', [AreaController::class, 'index']);
Route::get('/cities/popular', [CityController::class, 'popular']);
Route::get('/cities/suggest', [CityController::class, 'suggest']);
Route::get('/vacancies', [VacancyController::class, 'index']);
Route::get('/vacancies/{vacancy}', [VacancyController::class, 'show']);

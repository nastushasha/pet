<?php

use Illuminate\Support\Facades\Route;

// Список всех вакансий
Route::get('/vacancies', function () {
    return response()->json([
        'success' => true,
        'data' => [
            [
                'id' => 1,
                'title' => 'Python Developer',
                'company' => 'Google',
                'city' => 'Москва',
                'salary' => '200 000 - 250 000 ₽'
            ],
            [
                'id' => 2,
                'title' => 'PHP Developer',
                'company' => 'Yandex',
                'city' => 'Санкт-Петербург',
                'salary' => '180 000 - 220 000 ₽'
            ],
            [
                'id' => 3,
                'title' => 'Frontend Developer (React)',
                'company' => 'Tinkoff',
                'city' => 'Москва',
                'salary' => '150 000 - 200 000 ₽'
            ]
        ]
    ], 200, [], JSON_UNESCAPED_UNICODE);
});

// Одна вакансия по ID
Route::get('/vacancies/{id}', function ($id) {
    $vacancies = [
        1 => [
            'id' => 1,
            'title' => 'Pythoasdn Developer',
            'company' => 'Google',
            'city' => 'Москва',
            'salary_from' => 200000,
            'salary_to' => 250000,
            'currency' => 'RUB',
            'description' => 'Разработка на Python, Django. Опыт от 3 лет.',
            'skills' => ['Python', 'Django', 'PostgreSQL'],
            'url' => 'https://hh.ru/vacancy/123'
        ],
        2 => [
            'id' => 2,
            'title' => 'PHP Developer',
            'company' => 'Yandex',
            'city' => 'Санкт-Петербург',
            'salary_from' => 180000,
            'salary_to' => 220000,
            'currency' => 'RUB',
            'description' => 'Разработка на PHP, Laravel. Опыт от 2 лет.',
            'skills' => ['PHP', 'Laravel', 'MySQL'],
            'url' => 'https://hh.ru/vacancy/456'
        ]
    ];

    if (!isset($vacancies[$id])) {
        return response()->json([
            'success' => false,
            'message' => 'Вакансия не найдена'
        ], 404, [], JSON_UNESCAPED_UNICODE);
    }

    return response()->json([
        'success' => true,
        'data' => $vacancies[$id]
    ], 200, [], JSON_UNESCAPED_UNICODE);
});

// Тестовый маршрут (чтобы убедиться что API работает)
Route::get('/test', function () {
    return response()->json(['message' => 'API работает!'], 200, [], JSON_UNESCAPED_UNICODE);
});

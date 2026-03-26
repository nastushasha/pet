<?php

return [

    'base_url' => rtrim(env('HH_API_BASE_URL', 'https://api.hh.ru'), '/'),

    /*
    | Обязательно укажите контакт в User-Agent (требование hh.ru).
    | Пример: MyVacancyApp/1.0 (you@example.com)
    */
    'user_agent' => env('HH_USER_AGENT', ''),

    // Delay between detail requests to HH API (ms).
    'details_delay_ms' => (int) env('HH_DETAILS_DELAY_MS', 200),

];

<?php

namespace App\Http\Controllers;

use App\Services\Hh\HhApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CityController extends Controller
{
    public function popular(): JsonResponse
    {
        $client = HhApiClient::fromConfig();

        $names = [
            'Москва',
            'Санкт-Петербург',
            'Казань',
            'Новосибирск',
            'Екатеринбург',
            'Нижний Новгород',
            'Краснодар',
            'Ростов-на-Дону',
            'Самара',
            'Воронеж',
            'Уфа',
            'Красноярск',
            'Пермь',
            'Волгоград',
            'Челябинск',
            'Омск',
            'Тюмень',
            'Иркутск',
            'Владивосток',
            'Ярославль',
        ];

        $items = Cache::remember('hh.popular_cities.v4', now()->addHours(12), function () use ($client, $names) {
            $out = [];
            foreach ($names as $name) {
                $suggest = $client->suggestAreaLeaves($name);
                $first = $suggest['items'][0] ?? null;
                if (is_array($first) && isset($first['id'], $first['text'])) {
                    $out[] = ['id' => (string) $first['id'], 'name' => (string) $first['text']];
                }
            }

            // Unique by id
            $uniq = [];
            foreach ($out as $row) {
                $uniq[$row['id']] = $row;
            }

            return array_values($uniq);
        });

        return response()->json([
            'success' => true,
            'data' => $items,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function suggest(Request $request): JsonResponse
    {
        $text = trim((string) $request->query('text', ''));
        if ($text === '') {
            return response()->json(['success' => true, 'data' => []], 200, [], JSON_UNESCAPED_UNICODE);
        }

        $client = HhApiClient::fromConfig();
        $payload = $client->suggestAreaLeaves($text);

        $items = [];
        foreach (($payload['items'] ?? []) as $row) {
            if (! is_array($row) || ! isset($row['id'], $row['text'])) {
                continue;
            }

            $items[] = ['id' => (string) $row['id'], 'name' => (string) $row['text']];
        }

        return response()->json([
            'success' => true,
            'data' => $items,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}

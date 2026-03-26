<?php

namespace App\Http\Controllers;

use App\Services\Hh\HhApiClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class AreaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $root = (string) $request->query('root', '113'); // 113 — Россия

        $client = HhApiClient::fromConfig();
        $area = $client->getArea($root);

        $items = [];
        foreach (($area['areas'] ?? []) as $child) {
            if (! is_array($child)) {
                continue;
            }

            $id = isset($child['id']) ? (string) $child['id'] : null;
            $name = isset($child['name']) ? (string) $child['name'] : null;

            if ($id && $name) {
                $items[] = ['id' => $id, 'name' => $name];
            }
        }

        if ($items === []) {
            throw new RuntimeException('Не удалось получить список регионов из HH API.');
        }

        usort($items, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return response()->json([
            'success' => true,
            'data' => $items,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}

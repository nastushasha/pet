<?php

namespace App\Http\Controllers;

use App\Http\Resources\VacancyDetailResource;
use App\Http\Resources\VacancyListCollection;
use App\Models\Vacancy;
use App\Services\Hh\HhApiClient;
use App\Services\Hh\VacancyImporter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class VacancyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $q = Vacancy::query();
        $perPage = (int) $request->query('per_page', 20);
        $perPage = max(1, min($perPage, 500));

        $areasRaw = (string) $request->query('areas', '');
        if ($areasRaw !== '') {
            $areaIds = array_values(array_filter(array_map('trim', preg_split('/[,\s]+/', $areasRaw) ?: [])));
            if ($areaIds !== []) {
                $q->whereIn('area_id', $areaIds);
            }
        }

        $rows = $q
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return (new VacancyListCollection($rows))
            ->response()
            ->setStatusCode(200);
    }

    public function show(Vacancy $vacancy): JsonResponse
    {
        if ($vacancy->details_status !== 'ok') {
            try {
                $importer = new VacancyImporter(HhApiClient::fromConfig());
                $vacancy = $importer->fetchAndPersistDetails($vacancy);
            } catch (Throwable) {
            }
        }

        return response()->json([
            'success' => true,
            'data' => (new VacancyDetailResource($vacancy))->resolve(),
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}

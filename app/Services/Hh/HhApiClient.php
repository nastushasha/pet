<?php

namespace App\Services\Hh;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class HhApiClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly string $userAgent,
    ) {}

    public static function fromConfig(): self
    {
        $ua = (string) config('hh.user_agent');
        if ($ua === '') {
            throw new RuntimeException('Задайте HH_USER_AGENT в .env (например: MyApp/1.0 (you@example.com)).');
        }

        return new self((string) config('hh.base_url'), $ua);
    }

    /**
     * @return array{items: array<int, array<string, mixed>>, pages: int, page: int, found: int, per_page: int}
     */
    public function searchVacancies(array $query): array
    {
        $response = $this->http()->get('/vacancies', $query);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'HH API (поиск): '.$response->status().' — '.$response->body(),
                0,
                $e
            );
        }

        return $response->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getVacancy(string $hhVacancyId): array
    {
        $response = $this->http()->get('/vacancies/'.$hhVacancyId);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'HH API (вакансия '.$hhVacancyId.'): '.$response->status().' — '.$response->body(),
                0,
                $e
            );
        }

        return $response->json();
    }

    /**
     * @return array<string, mixed>
     */
    public function getArea(string $areaId): array
    {
        $response = $this->http()->get('/areas/'.$areaId);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'HH API (регион '.$areaId.'): '.$response->status().' — '.$response->body(),
                0,
                $e
            );
        }

        return $response->json();
    }

    /**
     * @return array{items: array<int, array<string, mixed>>}
     */
    public function suggestAreaLeaves(string $text): array
    {
        $response = $this->http()->get('/suggests/area_leaves', [
            'text' => $text,
        ]);

        try {
            $response->throw();
        } catch (RequestException $e) {
            throw new RuntimeException(
                'HH API (подсказки городов): '.$response->status().' — '.$response->body(),
                0,
                $e
            );
        }

        return $response->json();
    }

    private function http()
    {
        return Http::withHeaders([
            'User-Agent' => $this->userAgent,
            'Accept' => 'application/json',
        ])
            ->timeout(30)
            ->baseUrl($this->baseUrl);
    }
}

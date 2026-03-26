<?php

namespace App\Services\Hh;

use App\Jobs\FetchVacancyDetailsJob;
use App\Models\Vacancy;
use Carbon\CarbonImmutable;
use Illuminate\Support\Arr;

class VacancyImporter
{
    public function __construct(
        private readonly HhApiClient $client,
    ) {}

    /**
     * @return int Количество сохранённых/обновлённых записей
     */
    public function importSearchResults(
        string $text,
        ?string $areaId,
        int $pages,
        int $perPage,
        int $delayMsBetweenDetail,
        ?int $periodDays = null,
        string $detailsMode = 'queue',
    ): int {
        $pages = max(1, $pages);
        $perPage = min(100, max(1, $perPage));
        $saved = 0;

        for ($page = 0; $page < $pages; $page++) {
            $query = array_filter([
                'text' => $text,
                'area' => $areaId,
                'period' => $periodDays,
                'page' => $page,
                'per_page' => $perPage,
            ], fn ($v) => $v !== null && $v !== '');

            $batch = $this->client->searchVacancies($query);
            $items = $batch['items'] ?? [];

            foreach ($items as $item) {
                $hhId = (string) ($item['id'] ?? '');
                if ($hhId === '') {
                    continue;
                }

                $vacancy = $this->upsertShortFromSearchItem($item);
                if ($detailsMode === 'sync') {
                    if ($delayMsBetweenDetail > 0) {
                        usleep($delayMsBetweenDetail * 1000);
                    }
                    $this->fetchAndPersistDetails($vacancy);
                } elseif ($detailsMode === 'queue') {
                    FetchVacancyDetailsJob::dispatch($vacancy->id);
                }
                $saved++;
            }

            if (count($items) < $perPage) {
                break;
            }
        }

        return $saved;
    }

    public function fetchAndPersistDetails(Vacancy $vacancy): Vacancy
    {
        $attempts = (int) $vacancy->details_attempts + 1;
        $vacancy->details_attempts = $attempts;
        $vacancy->details_status = 'pending';
        $vacancy->save();

        try {
            $full = $this->client->getVacancy($vacancy->hh_id);

            return $this->upsertFromHhPayload($vacancy->toArray(), $full);
        } catch (\Throwable $e) {
            $vacancy->details_status = 'failed';
            $vacancy->details_error = mb_substr($e->getMessage(), 0, 2000);
            $vacancy->save();

            throw $e;
        }
    }

    /**
     * @param  array<string, mixed>  $short
     */
    private function upsertShortFromSearchItem(array $short): Vacancy
    {
        $hhId = (string) ($short['id'] ?? '');
        $salary = is_array($short['salary'] ?? null) ? $short['salary'] : null;
        $published = $short['published_at'] ?? null;
        $description = $this->snippetToText($short['snippet'] ?? null);

        return Vacancy::updateOrCreate(
            ['hh_id' => $hhId],
            [
                'name' => (string) ($short['name'] ?? 'Без названия'),
                'employer_name' => Arr::get($short, 'employer.name'),
                'area_id' => Arr::get($short, 'area.id'),
                'area_name' => Arr::get($short, 'area.name'),
                'salary_from' => $salary['from'] ?? null,
                'salary_to' => $salary['to'] ?? null,
                'salary_currency' => $salary['currency'] ?? null,
                'salary_gross' => isset($salary['gross']) ? (bool) $salary['gross'] : null,
                'description' => $description,
                'alternate_url' => $short['alternate_url'] ?? null,
                'published_at' => $published ? CarbonImmutable::parse((string) $published) : null,
                'details_status' => 'pending',
                'details_error' => null,
            ]
        );
    }

    /**
     * @param  array<string, mixed>  $short
     * @param  array<string, mixed>  $full
     */
    private function upsertFromHhPayload(array $short, array $full): Vacancy
    {
        $hhId = (string) ($full['id'] ?? $short['id'] ?? '');
        $salary = is_array($full['salary'] ?? null) ? $full['salary'] : ($short['salary'] ?? null);
        $salary = is_array($salary) ? $salary : null;

        $skills = [];
        foreach ($full['key_skills'] ?? [] as $row) {
            if (is_array($row) && isset($row['name'])) {
                $skills[] = $row['name'];
            }
        }

        $description = isset($full['description']) ? $this->htmlToPlainText((string) $full['description']) : null;
        if ($description === null || $description === '') {
            $description = $this->snippetToText($short['snippet'] ?? null);
        }

        $published = $full['published_at'] ?? $short['published_at'] ?? null;

        return Vacancy::updateOrCreate(
            ['hh_id' => $hhId],
            [
                'name' => (string) ($full['name'] ?? $short['name'] ?? 'Без названия'),
                'employer_name' => Arr::get($full, 'employer.name') ?? Arr::get($short, 'employer.name'),
                'area_id' => Arr::get($full, 'area.id') ?? Arr::get($short, 'area.id'),
                'area_name' => Arr::get($full, 'area.name') ?? Arr::get($short, 'area.name'),
                'experience_id' => $this->dictionaryId($full['experience'] ?? $short['experience'] ?? null),
                'experience_name' => $this->dictionaryName($full['experience'] ?? $short['experience'] ?? null),
                'employment_id' => $this->dictionaryId($full['employment'] ?? $short['employment'] ?? null),
                'employment_name' => $this->dictionaryName($full['employment'] ?? $short['employment'] ?? null),
                'schedule_id' => $this->dictionaryId($full['schedule'] ?? $short['schedule'] ?? null),
                'schedule_name' => $this->dictionaryName($full['schedule'] ?? $short['schedule'] ?? null),
                'vacancy_type_id' => $this->dictionaryId($full['type'] ?? $short['type'] ?? null),
                'vacancy_type_name' => $this->dictionaryName($full['type'] ?? $short['type'] ?? null),
                'department_name' => Arr::get($full, 'department.name') ?? Arr::get($short, 'department.name'),
                'address_text' => $this->formatAddress($full['address'] ?? $short['address'] ?? null),
                'has_test' => $this->toNullableBool($full['has_test'] ?? $short['has_test'] ?? null),
                'response_letter_required' => $this->toNullableBool($full['response_letter_required'] ?? $short['response_letter_required'] ?? null),
                'accept_temporary' => $this->toNullableBool($full['accept_temporary'] ?? $short['accept_temporary'] ?? null),
                'archived' => $this->toNullableBool($full['archived'] ?? $short['archived'] ?? null),
                'working_days' => $this->dictionaryNameList($full['working_days'] ?? $short['working_days'] ?? null),
                'working_time_intervals' => $this->dictionaryNameList($full['working_time_intervals'] ?? $short['working_time_intervals'] ?? null),
                'working_time_modes' => $this->dictionaryNameList($full['working_time_modes'] ?? $short['working_time_modes'] ?? null),
                'professional_roles' => $this->dictionaryNameList($full['professional_roles'] ?? $short['professional_roles'] ?? null),
                'salary_from' => $salary['from'] ?? null,
                'salary_to' => $salary['to'] ?? null,
                'salary_currency' => $salary['currency'] ?? null,
                'salary_gross' => isset($salary['gross']) ? (bool) $salary['gross'] : null,
                'description' => $description,
                'skills' => $skills,
                'alternate_url' => $full['alternate_url'] ?? $short['alternate_url'] ?? null,
                'published_at' => $published ? CarbonImmutable::parse((string) $published) : null,
                'details_status' => 'ok',
                'details_fetched_at' => now(),
                'details_error' => null,
            ]
        );
    }

    /**
     * @param  array<string, mixed>|null  $row
     */
    private function dictionaryId(?array $row): ?string
    {
        if (! is_array($row) || ! isset($row['id'])) {
            return null;
        }

        return (string) $row['id'];
    }

    /**
     * @param  array<string, mixed>|null  $row
     */
    private function dictionaryName(?array $row): ?string
    {
        if (! is_array($row) || ! isset($row['name'])) {
            return null;
        }

        $name = trim((string) $row['name']);

        return $name !== '' ? $name : null;
    }

    /**
     * @param  array<int, array<string, mixed>>|null  $rows
     * @return list<string>|null
     */
    private function dictionaryNameList(mixed $rows): ?array
    {
        if (! is_array($rows) || $rows === []) {
            return null;
        }

        if ($this->isAssociativeArray($rows)) {
            return null;
        }

        $out = [];
        foreach ($rows as $row) {
            if (is_array($row) && isset($row['name'])) {
                $n = trim((string) $row['name']);
                if ($n !== '') {
                    $out[] = $n;
                }
            }
        }

        return $out === [] ? null : array_values(array_unique($out));
    }

    private function toNullableBool(mixed $v): ?bool
    {
        if ($v === null) {
            return null;
        }

        if (is_bool($v)) {
            return $v;
        }

        if ($v === 'true' || $v === 1 || $v === '1') {
            return true;
        }

        if ($v === 'false' || $v === 0 || $v === '0') {
            return false;
        }

        return null;
    }

    private function formatAddress(mixed $address): ?string
    {
        if ($address === null) {
            return null;
        }

        if (is_string($address)) {
            $t = trim($address);

            return $t !== '' ? $t : null;
        }

        if (! is_array($address)) {
            return null;
        }

        if (! empty($address['raw'])) {
            $t = trim((string) $address['raw']);

            return $t !== '' ? $t : null;
        }

        $parts = array_filter([
            isset($address['city']) ? trim((string) $address['city']) : null,
            isset($address['street']) ? trim((string) $address['street']) : null,
            isset($address['building']) ? trim((string) $address['building']) : null,
        ]);

        if ($parts === []) {
            return null;
        }

        return implode(', ', $parts);
    }

    private function htmlToPlainText(string $html): string
    {
        $text = preg_replace('/<\/(p|div|br|li|h\d)>/i', "\n", $html) ?? $html;
        $text = strip_tags($text);

        return trim(html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    }

    /**
     * @param  array<string, mixed>|null  $snippet
     */
    private function snippetToText(?array $snippet): ?string
    {
        if (! is_array($snippet)) {
            return null;
        }

        $parts = array_filter([
            isset($snippet['requirement']) ? $this->htmlToPlainText((string) $snippet['requirement']) : null,
            isset($snippet['responsibility']) ? $this->htmlToPlainText((string) $snippet['responsibility']) : null,
        ]);

        $merged = trim(implode("\n\n", $parts));

        return $merged !== '' ? $merged : null;
    }

    /**
     * @param  array<mixed>  $arr
     */
    private function isAssociativeArray(array $arr): bool
    {
        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}

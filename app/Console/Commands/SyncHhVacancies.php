<?php

namespace App\Console\Commands;

use App\Services\Hh\HhApiClient;
use App\Services\Hh\VacancyImporter;
use Illuminate\Console\Command;
use Throwable;

class SyncHhVacancies extends Command
{
    protected $signature = 'hh:sync-vacancies
        {--text=laravel : Текст поиска (параметр text в API hh.ru)}
        {--area= : ID региона(ов): один id или список через запятую (например 1 или 1,2,3). Без опции регион не фильтруется}
        {--period= : За сколько дней искать (параметр period в HH API, например 7)}
        {--pages=1 : Сколько страниц выдачи загрузить (0-based page в API)}
        {--per-page=20 : Вакансий на страницу, макс. 100}
        {--details=queue : Режим деталей: queue|sync|none}
        {--delay=200 : Пауза между запросами полной карточки, мс (снижает риск лимитов)}';

    protected $description = 'Загрузить вакансии с hh.ru через официальный API и сохранить в БД';

    public function handle(): int
    {
        try {
            $client = HhApiClient::fromConfig();
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $text = (string) $this->option('text');
        $area = $this->option('area');
        $areaRaw = $area !== null && $area !== '' ? (string) $area : '';
        $areaIds = $areaRaw !== ''
            ? array_values(array_filter(array_map('trim', preg_split('/[,\s]+/', $areaRaw) ?: [])))
            : [];
        $pages = (int) $this->option('pages');
        $perPage = (int) $this->option('per-page');
        $delay = (int) $this->option('delay');
        $detailsMode = strtolower((string) $this->option('details'));
        if (! in_array($detailsMode, ['queue', 'sync', 'none'], true)) {
            $this->error('Опция --details должна быть queue|sync|none');

            return self::FAILURE;
        }
        $period = $this->option('period');
        $periodDays = $period !== null && $period !== '' ? (int) $period : null;

        $areaLabel = $areaIds !== [] ? implode(',', $areaIds) : 'не задан (вся выдача по запросу)';
        $periodLabel = $periodDays ? ", period={$periodDays}д" : '';
        $this->info("Поиск «{$text}», регион: {$areaLabel}{$periodLabel}, страниц: {$pages}, по {$perPage} на страницу, details={$detailsMode}.");

        $importer = new VacancyImporter($client);

        try {
            $n = 0;
            if ($areaIds === []) {
                $n += $importer->importSearchResults($text, null, $pages, $perPage, $delay, $periodDays, $detailsMode);
            } else {
                foreach ($areaIds as $areaId) {
                    $n += $importer->importSearchResults($text, (string) $areaId, $pages, $perPage, $delay, $periodDays, $detailsMode);
                }
            }
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Готово, обработано карточек: {$n}.");

        return self::SUCCESS;
    }
}

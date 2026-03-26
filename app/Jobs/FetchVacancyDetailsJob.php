<?php

namespace App\Jobs;

use App\Models\Vacancy;
use App\Services\Hh\HhApiClient;
use App\Services\Hh\VacancyImporter;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class FetchVacancyDetailsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(
        public int $vacancyId,
    ) {}

    public function handle(): void
    {
        $vacancy = Vacancy::query()->find($this->vacancyId);
        if (! $vacancy) {
            return;
        }

        // Basic in-job throttle to reduce HH API pressure.
        $delayMs = max(0, (int) config('hh.details_delay_ms', 200));
        if ($delayMs > 0) {
            usleep($delayMs * 1000);
        }

        $importer = new VacancyImporter(HhApiClient::fromConfig());
        $importer->fetchAndPersistDetails($vacancy);
    }

    public function failed(Throwable $exception): void
    {
        $vacancy = Vacancy::query()->find($this->vacancyId);
        if (! $vacancy) {
            return;
        }

        $vacancy->details_status = 'failed';
        $vacancy->details_error = mb_substr($exception->getMessage(), 0, 2000);
        $vacancy->save();
    }
}

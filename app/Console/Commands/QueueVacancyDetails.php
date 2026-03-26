<?php

namespace App\Console\Commands;

use App\Jobs\FetchVacancyDetailsJob;
use App\Models\Vacancy;
use Illuminate\Console\Command;

class QueueVacancyDetails extends Command
{
    protected $signature = 'hh:queue-details
        {--limit=500 : Максимум вакансий за запуск}
        {--force-failed : Включить failed в повторную очередь}';

    protected $description = 'Поставить в очередь догрузку деталей вакансий HH';

    public function handle(): int
    {
        $limit = max(1, (int) $this->option('limit'));
        $includeFailed = (bool) $this->option('force-failed');

        $q = Vacancy::query()->orderByDesc('published_at')->orderByDesc('id');
        if ($includeFailed) {
            $q->whereIn('details_status', ['pending', 'failed']);
        } else {
            $q->where('details_status', 'pending');
        }

        $rows = $q->limit($limit)->get(['id']);
        foreach ($rows as $row) {
            FetchVacancyDetailsJob::dispatch((int) $row->id);
        }

        $this->info('Поставлено в очередь: '.$rows->count());

        return self::SUCCESS;
    }
}

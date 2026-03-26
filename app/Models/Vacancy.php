<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vacancy extends Model
{
    protected $fillable = [
        'hh_id',
        'name',
        'employer_name',
        'area_id',
        'area_name',
        'experience_id',
        'experience_name',
        'employment_id',
        'employment_name',
        'schedule_id',
        'schedule_name',
        'vacancy_type_id',
        'vacancy_type_name',
        'department_name',
        'address_text',
        'has_test',
        'response_letter_required',
        'accept_temporary',
        'archived',
        'working_days',
        'working_time_intervals',
        'working_time_modes',
        'professional_roles',
        'details_status',
        'details_attempts',
        'details_fetched_at',
        'details_error',
        'salary_from',
        'salary_to',
        'salary_currency',
        'salary_gross',
        'description',
        'skills',
        'alternate_url',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'salary_gross' => 'boolean',
            'skills' => 'array',
            'working_days' => 'array',
            'working_time_intervals' => 'array',
            'working_time_modes' => 'array',
            'professional_roles' => 'array',
            'details_fetched_at' => 'datetime',
            'has_test' => 'boolean',
            'response_letter_required' => 'boolean',
            'accept_temporary' => 'boolean',
            'archived' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function salaryLabel(): ?string
    {
        if ($this->salary_from === null && $this->salary_to === null) {
            return null;
        }

        $cur = match ($this->salary_currency) {
            'RUR', 'RUB' => '₽',
            'USD' => '$',
            'EUR' => '€',
            default => $this->salary_currency ? ' '.$this->salary_currency : '',
        };

        $gross = $this->salary_gross === true ? ' до вычета налогов' : ($this->salary_gross === false ? ' на руки' : '');

        if ($this->salary_from !== null && $this->salary_to !== null) {
            return number_format($this->salary_from, 0, '', ' ')
                .' — '
                .number_format($this->salary_to, 0, '', ' ')
                ." {$cur}{$gross}";
        }

        if ($this->salary_from !== null) {
            return 'от '.number_format($this->salary_from, 0, '', ' ')." {$cur}{$gross}";
        }

        return 'до '.number_format((int) $this->salary_to, 0, '', ' ')." {$cur}{$gross}";
    }

    private function formatDateTime(?\DateTimeInterface $date): ?string
    {
        return $date?->format('d/m/Y • H:i');
    }

    /**
     * @return list<array{label: string, value: string}>
     */
    public function metaRows(): array
    {
        $rows = [];

        if ($this->experience_name) {
            $rows[] = ['label' => 'Опыт работы', 'value' => $this->experience_name];
        }
        if ($this->employment_name) {
            $rows[] = ['label' => 'Занятость', 'value' => $this->employment_name];
        }
        if ($this->schedule_name) {
            $rows[] = ['label' => 'График', 'value' => $this->schedule_name];
        }
        if ($this->vacancy_type_name) {
            $rows[] = ['label' => 'Тип вакансии', 'value' => $this->vacancy_type_name];
        }
        if ($this->department_name) {
            $rows[] = ['label' => 'Подразделение', 'value' => $this->department_name];
        }
        if ($this->address_text) {
            $rows[] = ['label' => 'Адрес', 'value' => $this->address_text];
        }
        if ($this->published_at) {
            $rows[] = ['label' => 'Опубликована', 'value' => $this->formatDateTime($this->published_at)];
        }

        $roles = $this->professional_roles;
        if (is_array($roles) && $roles !== []) {
            $rows[] = ['label' => 'Профессиональные роли', 'value' => implode(' · ', $roles)];
        }

        $wd = $this->working_days;
        if (is_array($wd) && $wd !== []) {
            $rows[] = ['label' => 'Рабочие дни', 'value' => implode(' · ', $wd)];
        }

        $wti = $this->working_time_intervals;
        if (is_array($wti) && $wti !== []) {
            $rows[] = ['label' => 'Интервалы времени', 'value' => implode(' · ', $wti)];
        }

        $wtm = $this->working_time_modes;
        if (is_array($wtm) && $wtm !== []) {
            $rows[] = ['label' => 'Режим времени', 'value' => implode(' · ', $wtm)];
        }

        if ($this->has_test === true) {
            $rows[] = ['label' => 'Тестовое задание', 'value' => 'Есть'];
        }
        if ($this->response_letter_required === true) {
            $rows[] = ['label' => 'Сопроводительное письмо', 'value' => 'Обязательно'];
        }
        if ($this->accept_temporary === true) {
            $rows[] = ['label' => 'Временное оформление / ГПХ', 'value' => 'Да'];
        }
        if ($this->archived === true) {
            $rows[] = ['label' => 'Статус', 'value' => 'В архиве'];
        }

        return $rows;
    }

    public function toListPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->name,
            'company' => $this->employer_name ?? '—',
            'city' => $this->area_name ?? '—',
            'area_id' => $this->area_id,
            'salary' => $this->salaryLabel(),
            'experience' => $this->experience_name,
            'employment' => $this->employment_name,
            'schedule' => $this->schedule_name,
            'published_at_label' => $this->formatDateTime($this->published_at),
        ];
    }

    public function toDetailPayload(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->name,
            'company' => $this->employer_name ?? '—',
            'city' => $this->area_name ?? '—',
            'salary_label' => $this->salaryLabel(),
            'details_status' => $this->details_status,
            'salary_from' => $this->salary_from,
            'salary_to' => $this->salary_to,
            'currency' => $this->salary_currency === 'RUR' ? 'RUB' : ($this->salary_currency ?? 'RUB'),
            'published_at_label' => $this->formatDateTime($this->published_at),
            'meta' => $this->metaRows(),
            'description' => $this->description ?? '',
            'skills' => $this->skills ?? [],
            'url' => $this->alternate_url,
        ];
    }
}

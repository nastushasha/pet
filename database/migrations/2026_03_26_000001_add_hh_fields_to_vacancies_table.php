<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->string('experience_id', 64)->nullable()->after('area_name');
            $table->string('experience_name')->nullable()->after('experience_id');
            $table->string('employment_id', 64)->nullable()->after('experience_name');
            $table->string('employment_name')->nullable()->after('employment_id');
            $table->string('schedule_id', 64)->nullable()->after('employment_name');
            $table->string('schedule_name')->nullable()->after('schedule_id');
            $table->string('vacancy_type_id', 64)->nullable()->after('schedule_name');
            $table->string('vacancy_type_name')->nullable()->after('vacancy_type_id');
            $table->string('department_name')->nullable()->after('vacancy_type_name');
            $table->text('address_text')->nullable()->after('department_name');
            $table->boolean('has_test')->nullable()->after('address_text');
            $table->boolean('response_letter_required')->nullable()->after('has_test');
            $table->boolean('accept_temporary')->nullable()->after('response_letter_required');
            $table->boolean('archived')->nullable()->after('accept_temporary');
            $table->json('working_days')->nullable()->after('archived');
            $table->json('working_time_intervals')->nullable()->after('working_days');
            $table->json('working_time_modes')->nullable()->after('working_time_intervals');
            $table->json('professional_roles')->nullable()->after('working_time_modes');
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn([
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
            ]);
        });
    }
};

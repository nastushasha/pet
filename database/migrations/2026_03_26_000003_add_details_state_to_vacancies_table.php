<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->string('details_status', 16)->default('pending')->after('professional_roles');
            $table->unsignedInteger('details_attempts')->default(0)->after('details_status');
            $table->timestamp('details_fetched_at')->nullable()->after('details_attempts');
            $table->text('details_error')->nullable()->after('details_fetched_at');
        });
    }

    public function down(): void
    {
        Schema::table('vacancies', function (Blueprint $table) {
            $table->dropColumn([
                'details_status',
                'details_attempts',
                'details_fetched_at',
                'details_error',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vacancies', function (Blueprint $table) {
            $table->id();
            $table->string('hh_id')->unique();
            $table->string('name');
            $table->string('employer_name')->nullable();
            $table->string('area_name')->nullable();
            $table->unsignedInteger('salary_from')->nullable();
            $table->unsignedInteger('salary_to')->nullable();
            $table->string('salary_currency', 8)->nullable();
            $table->boolean('salary_gross')->nullable();
            $table->text('description')->nullable();
            $table->json('skills')->nullable();
            $table->string('alternate_url')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vacancies');
    }
};

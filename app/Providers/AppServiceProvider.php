<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // В Docker БД доступна по имени сервиса, а не по 127.0.0.1. Смонтированный с хоста
        // bootstrap/cache/config.php или .env часто оставляют DB_HOST=127.0.0.1 — чиним на лету.
        if (! file_exists('/.dockerenv')) {
            return;
        }

        if (config('database.default') !== 'pgsql') {
            return;
        }

        $host = config('database.connections.pgsql.host');
        if (in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) {
            config(['database.connections.pgsql.host' => 'postgres']);
        }
    }
}

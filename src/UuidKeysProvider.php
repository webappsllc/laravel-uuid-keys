<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Illuminate\Support\ServiceProvider;

class UuidKeysProvider extends ServiceProvider {
    /**
     * Allows for migrations to be published.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations')
        ], 'migrations');
    }
}

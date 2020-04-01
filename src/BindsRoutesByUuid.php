<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Illuminate\Support\Facades\Route;

trait BindsRoutesByUuid {
    public static function booted() : void {
        Route::bind($model->getTable(), function ($uuid) {
            return static::whereId($uuid)->first();
        });
    }
}

<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Illuminate\Support\Facades\Route;

trait BindsRoutesByUuid {
    public static function booted() : void {
        $model = new static;

        Route::bind($model->getTable(), function ($uuid) {
            return static::whereUuid($uuid)->first();
        });
    }
}

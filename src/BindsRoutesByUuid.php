<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Illuminate\Support\Facades\Route;

/**
 * This trait implements model route binding.
 *
 * @needs Webapps\Models\Support\UuidKeys;
 */
trait BindsRoutesByUuid {

    abstract public function uuidColumn(): string;

    /**
     * Defines the column to use for model route binding.
     *
     * @return string
     */
    public function getRouteKeyName() {
        return $this->uuidColumn();
    }

    public static function booted() : void {
        $model = new static;

        Route::bind($model->getTable(), function ($uuid) {
            return static::whereUuid($uuid)->first();
        });
    }
}

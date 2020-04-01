<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Illuminate\Support\Facades\Route;
use Dyrynda\Database\Support\GeneratesUuid;

/**
 * Allows models to easily use uuids as model keys.
 *
 * Every model should define the following:
 * $casts - Must be include every uuid key including the primary 'id' as the type 'uuid'.
 *
 * @see - https://github.com/michaeldyrynda/laravel-model-uuid
 * @see - https://github.com/michaeldyrynda/laravel-efficient-uuid
 */

trait UuidKeys {
    use GeneratesUuid;

    /**
     * Sets the type of uuid to use. Has good tradeoffs that improve reads
     * at some cost on writes.
     */
    protected $uuidVersion = 'ordered';
    protected static $_uuidColumns = null;

    /**
      When using this trait be sure to add this mapping if overriding the $casts variable.
    */
    //protected $casts = ['id' => Dyrynda\Database\Casts\EfficientUuid];

    public function uuidColumn(): string
    {
        return 'id';
    }

    public function uuidColumns() : array {
        return static::$_uuidColumns;
    }

    public static function booted() : void {
        static::bootGeneratesUuid();
        $model = new static;
        if (is_null(static::$_uuidColumns)) {
            static::$_uuidColumns = [];
            foreach($model->getCasts() as $column => $type) {
                if($type === 'uuid') {
                    static::$_uuidColumns[] = $column;
                }
            }
        }

        Route::bind($model->getTable(), function ($uuid) {
            return static::whereId($uuid)->first();
        });
    }
}

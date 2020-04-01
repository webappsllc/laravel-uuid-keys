<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use LogicException;
use Dyrynda\Database\Support\GeneratesUuid;

/**
 * Allows models to easily use uuids as model keys.
 *
 * Every model should define the following:
 * $casts - Must be include every uuid key including the primary 'id' as the type 'uuid'.
 * $keyType - Must be set to string
 * $incrementing - Must be set to false
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

    public function uuidColumn(): string
    {
        return 'id';
    }

    public function uuidColumns() : array {
        return static::$_uuidColumns;
    }

    /**
      Populates the uuidColumns list and boots the sub-trait.
     */
    public static function booted() : void {
        static::bootGeneratesUuid();
        $model = new static;
        if($model->keyType !== 'string') {
            throw new LogicException("Property \$keyType must be set to 'string' on model " . get_class($model) . ".");
        }
        if($model->incrementing) {
            throw new LogicException("Property \$incrementing must be set to 'false' on model " . get_class($model) . ".");
        }
        if (is_null(static::$_uuidColumns)) {
            static::$_uuidColumns = [];
            foreach($model->getCasts() as $column => $type) {
                if(is_a($type, EfficientUuid::class) || $type === 'uuid') {
                    static::$_uuidColumns[] = $column;
                }
            }
        }
    }

}

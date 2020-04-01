<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Illuminate\Support\Facades\Route;
use Dyrynda\Database\Support\GeneratesUuid;
use Dyrynda\Database\Casts\EfficientUuid;

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
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Sets the type of uuid to use. Has good tradeoffs that improve reads
     * at some cost on writes.
     */
    protected $uuidVersion = 'ordered';
    protected static $_uuidColumns = null;

    /**
      When using this trait be sure to add this mapping if overriding the $casts variable.
    */
    protected $casts = [
        'id' => EfficientUuid::class
    ];

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
    public static function booting() : void {
        static::bootGeneratesUuid();
        $model = new static;
        if (is_null(static::$_uuidColumns)) {
            static::$_uuidColumns = [];
            foreach($model->getCasts() as $column => $type) {
                if($type === 'uuid' || is_a($type, EfficientUuid::class)) {
                    static::$_uuidColumns[] = $column;
                }
            }
        }
    }

}

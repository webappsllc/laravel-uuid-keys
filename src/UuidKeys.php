<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use LogicException;
use Dyrynda\Database\Support\GeneratesUuid;

/**
 * Allows models to easily use uuids as model keys.
 *
 * Every model should define the following:
 * $casts - Must be include every uuid key including the default 'uuid' column as the type EfficientUuid.
 * $keyType - Must be set to string
 * $incrementing - Must be set to false
 *
 * @see - https://github.com/michaeldyrynda/laravel-model-uuid
 * @see - https://github.com/michaeldyrynda/laravel-efficient-uuid

 */
trait UuidKeys {
    use GeneratesUuid {
        bootGeneratesUuid as originalBootGeneratesUuid;
    }

    /**
     * Sets the type of uuid to use. Has good tradeoffs that improve reads
     * at some cost on writes.
     */
    protected $uuidVersion = 'ordered';
    protected static $_uuidColumns = null;

    public function uuidColumn(): string
    {
        return 'uuid';
    }

    public function uuidColumns() : array {
        return static::$_uuidColumns;
    }

    /**
      Allows the uuid to be used as the route id.
     */
    public function getRouteKeyName() {
        return 'uuid';
    }

    public static function findByUuid($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return static::whereUuid($id)->get($columns);
        }

        return static::whereUuid($id)->first($columns);
    }

    /**
      Populates the uuidColumns list and boots the sub-trait.
     */
    public static function booted() : void {
        static::bootGeneratesUuid();
        $model = new static;
        if (is_null(static::$_uuidColumns)) {
            static::$_uuidColumns = [];
            foreach($model->getCasts() as $column => $type) {
                if($type === EfficientUuid::class || $type === 'uuid') {
                    static::$_uuidColumns[] = $column;
                }
            }

            if(empty(static::$_uuidColumns)) {
                static::$_uuidColumns[] = $model->uuidColumn();
            }
        }
    }

    public static function bootGeneratesUuid(): void
    {
        static::saving(function ($model) {
            foreach ($model->uuidColumns() as $item) {
                /* @var \Illuminate\Database\Eloquent\Model|static $model */
                $uuid = $model->resolveUuid();

                if (isset($model->attributes[$item]) && ! is_null($model->attributes[$item])) {
                    /* @var \Ramsey\Uuid\Uuid $uuid */
                    try {
                        $uuid = $uuid->fromString(strtolower($model->attributes[$item]));
                    } catch (InvalidUuidStringException $e) {
                        $uuid = $uuid->fromBytes($model->attributes[$item]);
                    }
                }

                $model->{$item} = strtolower($uuid->toString());
            }
        });
    }

}

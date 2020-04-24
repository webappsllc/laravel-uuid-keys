<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

/**
 * UUID generation trait. Allows models to easily use uuids as model keys.
 *
 * Include this trait in any Eloquent model where you wish to automatically set
 * a UUID field. When saving, if the UUID field has not been set, generate a
 * new UUID value, which will be set on the model and saved by Eloquent.
 *
 * Every model should define the following:
 * $casts - Must be include every uuid key including the default 'uuid' column as the type EfficientUuid.
 *
 * @see - https://github.com/michaeldyrynda/laravel-model-uuid (Original Version)
 * @see - https://github.com/michaeldyrynda/laravel-efficient-uuid
 *
 * @property  string  $uuidVersion
 */
trait UuidKeys {

    /**
     * The version of uuid to use. Orederd is the default.
     *
     * @var array
     */
    protected static string $uuidVersion = 'ordered';

    /**
     * The factory used to create uuids.
     *
     * @var UuidFactory
     */
    protected static ?UuidFactory $uuidFactory;


    /**
     * The list of columns that are uuids. This is generated if not set.
     *
     * @var array
     */
    protected static ?array $_uuidColumns = null;

    /**
     
* Determine whether an attribute should be cast to a native type.
     *
     * @param  string  $key
     * @param  array|string|null  $types
     * @return bool
     */
    abstract public function hasCast($key, $types = null);

    /**
     * Defines the primary uuid column.
     *
     * @return string
     */
    public function uuidColumn(): string
    {
        return 'uuid';
    }

    /**
     * Returns the list of all uuid colums.
     *
     * @return array
     */
    public function uuidColumns() : array {
        return static::$_uuidColumns;
    
}

    /**
     * Returns the list of all uuid columns that should generate values when null.
     * Defaults to the primary uuid Column.
     *
     * @return array
     */
    public function generatedUuidColumns() : array {
        return [$this->uuidColumn()];
    }

    /**
     * Set a given attribute on the model. Handle uuid columns here then forward to parent.
     *
     * @param string $key
     * @param mixed $value
     * 
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if(in_array($key, $this->uuidColumns())) {
            if($value instanceof Uuid) {
                $value = $value->toString();
            } else {
                $value = strtolower($value);
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Resolve a UUID instance for the configured version.
     *
     * @return \Ramsey\Uuid\Uuid
     */
    public function resolveUuid(): Uuid {
        return static::$uuidFactory->makeUuid();
    }

    /**
     * Using the primary uuid column find records with the included columns.
     *
     * @param $id - The id value(s) to search by 
     * @param $columns - The columns to return in the response
     *
     * @return Model
     */
    public static function findByUuid($id, $columns = ['*'])
    {
        if (is_array($id) || $id instanceof Arrayable) {
            return static::whereUuid($id)->get($columns);
        }

        return static::whereUuid($id)->first($columns);
    }

    /**
     * Scope queries to find by UUID.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  string  $uuid
     * @param  string  $uuidColumn
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereUuid($query, $uuid, $uuidColumn = null): Builder
    {
        $uuidColumn = ! is_null($uuidColumn) && in_array($uuidColumn, $this->uuidColumns())
            ? $uuidColumn
            : $this->uuidColumn();

        $uuid = array_map(function ($uuid) {
            return Str::lower($uuid);
        }, Arr::wrap($uuid));

        if ($this->isClassCastable($uuidColumn)) {
            $uuid = static::$uuidFactory->bytesFromString($uuid);
        }

        if(count($uuid) > 1) {
            return $query->whereIn($uuidColumn, $uuid);
        } else {
            return $query->where($uuidColumn, $uuid[0]);
        }
    }

    /**
     * Populates the uuidColumns list.
     * Creates callback to save 
     */
    public static function booted() : void {
        static::$uuidFactory = UuidFactory::forVersion(static::$uuidVersion);

        $model = new static;

        //Fill the uuidColumns property.
        if (is_null(static::$_uuidColumns)) {
            static::$_uuidColumns = [];
            foreach($model->getCasts() as $column => $type) {
                if($type === EfficientUuid::class) {
                    static::$_uuidColumns[] = $column;
                }
            }

            if(empty(static::$_uuidColumns)) {
                static::$_uuidColumns[] = $model->uuidColumn();
            }
        }

        //Setup saving callback to generate uuids
        static::saving(function ($model) {
            foreach ($model->generatedUuidColumns() as $item) {
                if (!isset($model->attributes[$item]) || is_null($model->attributes[$item])) {
                    $model->{$item} = $model->resolveUuid(); 
                }
            }
        });
    }

}

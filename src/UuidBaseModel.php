<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Casts\EfficientUuid;

abstract class UuidBaseModel extends Model {
    use UuidKeys {
        booted as bootedUuidKeys;
    }

    use BindsRoutesByUuid {
        booted as bootedBindsRoutesByUuid;
    }

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
      When using this trait be sure to add this mapping if overriding the $casts variable.
    */
    protected $casts = [
        'id' => EfficientUuid::class
    ];

    public static function booted() {
        static::bootedUuidKeys();
        static::bootedBindsRoutesByUuid();
    }
}

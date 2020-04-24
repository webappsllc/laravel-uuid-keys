<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use UnexpectedValueException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;

class UuidFactory {
    
    /**
     * The supported UUID versions.
     *
     * @var array
     */
    protected static array 
$supportedVersions = [
        'uuid1' => [Uuid::class, 'uuid1'],
        'uuid3' => [Uuid::class, 'uuid3'],
        'uuid4' => [Uuid::class, 'uuid4'],
        'uuid5' => [Uuid::class, 'uuid5'],
        'uuid6' => [Uuid::class, 'uuid6'],
        'ordered' => [Str::class, 'orderedUuid'],
    ];

    protected static array $instances = [];

    protected string $version;
    protected $maker;

    protected function __construct(string $version) {
        $this->version = $version;
        $this->maker = (static::$supportedVersions[$this->version]);
    }

    /**
     * Resolve a UUID instance for the configured version.
     *
     * @return \Ramsey\Uuid\Uuid
     */
    public function makeUuid(): Uuid {
        return ($this->maker)($this->version);
    }

    /**
     * Convert a single UUID or array of UUIDs to bytes.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array|string  $uuid
     * @return array
     */
    public function bytesFromString($uuid): array
    {
        if (is_array($uuid) || $uuid instanceof Arrayable) {
            array_walk($uuid, function (&$uuid) {
                $uuid = $this->makeUuid()->fromString($uuid)->getBytes();
            });

            return $uuid;
        }

        return Arr::wrap($this->resolveUuid()->fromString($uuid)->getBytes());
    }

    /**
     * Get the UuidFactory for verison.
     */
    public static function forVersion(string $version) : UuidFactory {
        if(!isset(static::$supportedVersions[$version])) {
            throw new UnexpectedValueException("Cannot create uuids with version [{$version}]");
        }

        if(!isset(static::$instances[$version])) {
            static::$instances[$version] = new static($version);
        }

        return static::$instances[$version];
    }
}

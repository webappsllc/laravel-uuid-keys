<?php declare(strict_types=1);

namespace Webapps\Models\Support;

use Dyrynda\Database\Support\GeneratesUuid;

/**
 * Allows models to easily use uuids as model keys.
 *
 * Every model should define the following:
 * $casts - Must be include every uuid key including the primary 'id' as the type 'uuid'.
 * public function uuidColumns - Must be defined and return an array of all uuid keys including primary key
 *
 * Additionally the route binding must be defined in \App\Providers\RouteServiceProvider
 *
 * Route::bind('post', function ($post) {
 *     return \App\Post::whereUuid($post)->first();
 * });
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

    /**
      When using this trait be sure to add this mapping if overriding the $casts variable.
    */
    //protected $casts = ['id' => 'uuid'];

    public function uuidColumn(): string
    {
        return 'id';
    }
}

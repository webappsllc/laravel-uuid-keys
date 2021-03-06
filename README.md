# Laravel Uuid Keys

This packages attempts to make using uuids for all your keys as seamless and painfree as possible. There's still quite a bit of ceremony to get everything setup, but after that it's not bad.

This package depends on 2 existing laravel packages:
- [Laravel Efficient UUIDs](https://github.com/michaeldyrynda/laravel-efficient-uuid)
- [Laravel Model UUIDs](https://github.com/michaeldyrynda/laravel-model-uuid)

The point of _this_ packages is to steamline things when you want _all_ your models to use uuids for all keys. If you don't want that then use one of the previously mentioned packages.

These packages can also be referenced for more information.

## Installation

Add this repository to your composer.json file and install packages.

```json
"repositories": [{
    "type": "vcs",
    "url": "https://github.com/webappsllc/laravel-uuid-keys"
}],
"require": {
    "laravel-uuid-keys": "dev-master"
},
```

```shell
composer install
```

## Using Uuids

### Migrating the Database

When migrating just use `efficentUuid` as the column type.

```php
Schema::create('comments', function (Blueprint $table) {
    $table->efficientUuid('id')->primary();
    $table->efficientUuid('post_id')->index();
    $table->text('body');
    $table->timestamps();
});
```

Refer to [Laravel Efficient UUIDs](https://github.com/michaeldyrynda/laravel-efficient-uuid) for more details.

### Uuids in Models

There are 2 steps to making models work with uuids properly.

1. Use the `Webapps\Models\Support\UuidKeys` trait.
2. Add the uuid columns to the $casts property array with the type of `uuid`

Example Model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Webapps\Models\Support\UuidKeys;

class Comment extends Model
{
    use UuidKeys;
    
    // Include all uuid columns in addition to other casts
    protected $casts = [
        'id' => 'uuid',
        'post_id' => 'uuid'
    ];
}
```

Refer to [Laravel Model UUIDs](https://github.com/michaeldyrynda/laravel-model-uuid) for more details.

### Eloquent Queries

Using these columns requires alternative eloquent methods.

```php
$comment = Comment::whereUuid($uuid)->find();
$post = Comment::whereUuid($uuid, 'post_id');
```

Refer to [Laravel Model UUIDs](https://github.com/michaeldyrynda/laravel-model-uuid) for more details.

## Working with the Database Directly

This package includes a migration that adds helper functions to the database. This is because MySQL versions before 8 didn't include any handy functions for dealing with uuids directly.

These functions should be compatible with the ordered version of UUID_TO_BIN e.g. `UUID_TO_BIN(,true)`

### Create Functions

```shell
php artisan vendor:publish --tag=migrations
php artisan migrate
```

### Use Functions

```sql
insert into comments (id,post_id,body) values (uuid2bin(uuid()), uuid2bin('555f470a-33f8-11ea-850d-2e728ce88125'), 'Comment Body');
select bin2uuid(id) as id, bin2uuid(post_id) as post_id, body from some_table;
```

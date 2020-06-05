Yii2 Dependency Injection Enhancements
======================================

Yii2-di provides some enhancements to [Yii2's](http://www.yiiframework.com)
dependency injection framework.


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

```
php composer.phar require --prefer-dist thamtech/yii2-di
```

or add

```
"thamtech/yii2-di": "*"
```

to the `require` section of your `composer.json` file.

Usage
-----

### Instance

The `Instance::ensureAny()` method can be used in place of
[Instance::ensure()](https://www.yiiframework.com/doc/api/2.0/yii-di-instance#ensure%28%29-detail)
if you would like to ensure that an object is one of any number of
specified types.

For example,

```php
use yii\db\Connection;

// returns Yii::$app->db
$db = \thamtech\di\Instance::ensure('db', [
    Connection::class,
    \yii\redis\Connection::class,
]);

// returns an instance of Connection using the given configuration.
//     * the first type listed is used as the 'class' parameter when it isn't
//       specified in the reference array
$db = \thamtech\di\Instance::ensureAny([
    'dsn' => 'sqlite:path/to/my.db'
], [
    Connection::class,
    \yii\redis\Connection::class,
])
```

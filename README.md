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

### Instance Provider

The `InstanceProvider` class adds support for using providers in your dependency
injection implementation. This can be used for several purposes, such as
lazy or optional retrieval of an instance or as a marker for certain
instance types.


#### Lazy/Optional Retrieval

You may want to inject an object into a class, but the class may not need
the object instantiated yet (or ever). In this case, you can inject a provider
instead and let your class decide if and when to `get()` the object instance.

For example,

```php
class ExpensiveInstanceProvider implements \thamtech\di\Provider
{
    public function get()
    {
        // instantiate $object
        // ...
        return $objectInstance;
    }
}

class DependendClass
{
    private $provider;
    public function __construct(ExpensiveInstanceProvider $provider)
    {
        $this->provider = $provider;
    }
    
    public function fooBar()
    {
        $objectInstance = $provider->get();
    }
}
```

#### Provider as Marker Class

Java developers might be used to using annotations on abstractly typed
parameters to specify particular versions of the instance.

For example,

```java
class FooCacheUtil {
    private Cache cache;
    // dependency injection provides the '@foo' cache component
    public FooCache(@foo Cache cache) {
        this.cache = cache;
    }
}

class BarCacheUtil {
    private Cache cache;
    // dependency injection provides the @bar' cache component
    public BarCache(@bar Cache cache) {
        this.cache = cache;
    }
}
```

To accomplish something similar with Yii's dependency injection, you can
create marker class implementations of `InstanceProvider` and define your
marked components in the dependency injection container.

For example,

```php
namespace example\package;
class FooCacheProvider extends \thamtech\di\InstanceProvider {}
class BarCacheProvider extends \thamtech\di\InstanceProvider {}

class FooCacheUtil
{
    private $cache;
    // dependency injection provides a FooCacheProvider
    public function __construct(FooCacheProvider $cacheProvider)
    {
        $this->cache = $cacheProvider->get();
    }
}

class BarCacheUtil
{
    private $cache;
    // dependency injection provides a BarCacheProvider
    public function __construct(BarCacheProvider $cacheProvider)
    {
        $this->cache = $cacheProvider->get();
    }
}

// application's container configuration
'container' => [
    'singletons' => [
        'example\package\FooCache:::provided' => [
            'class' => 'yii\caching\ArrayCache',
        ],
        'example\package\BarCache:::provided' => [
            'class' => 'yii\caching\DbCache',
        ],
    ],
],
```

In the example above, the `FooCacheProvider` and `BarCacheProvider`
subclasses of `InstanceProvider` are used as marker classes that indicate
which type of cache component to provide via dependency injection.

The default implementation of `InstanceProvider` will look for
instances defined in the container with a name corresponding to the
subclass provider's class name. The `Provider` suffix is dropped, and
a `:::provided` suffix is appended.

So the `example\package\FooCacheProvider`'s `get()` method is going to use the
container to look for an instance defined as
`example\package\FooCache:::provided`.

You can override this behavior by overriding one or more of `$class`,
`$params`, and `$config` properties when you implement your `InstanceProvider`
subclass.

For example,

```php
namespace example\package;
class FooCacheProvider extends \thamtech\di\InstanceProvider
{
    public $class = 'yii\caching\ArrayCache';
    
    public $config = [
        'serializer' => false,
        'defaultDuration' => 3600,
    ];
}
```

##### Type Safety

As seen above, you can configure the provider to return objects of any
type via overriding class properties or via container definitions. You may
want to enforce a certain level of type safety.

For example, if `FooCacheProvider` should only return certain cache types, you
could specify it as follows:

```php
namespace example\package;
class FooCacheProvider extends \thamtech\di\InstanceProvider
{
    public $ensureTypes = [
        'yii\caching\ArrayCache',
        'yii\caching\FileCache',
    ];
}
```

This will ensure that your application will throw an exception if someone
accidentally configures it to return something else instead:

```php
'container' => [
    'singletons' => [
        'example\package\FooCache:::provided' => [
            'class' => 'yii\caching\DbCache',
        ],
    ],
],

$fooCacheProvider = new FooCacheProvider();
$fooCacheProvider->get(); // will throw an InvalidConfigException
```

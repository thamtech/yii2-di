<?php

namespace thamtechunit\di;

use thamtech\di\InstanceProvider;
use Yii;

class CacheProvider extends InstanceProvider {}
class DbProvider extends InstanceProvider {
    public $class = 'myDb';
}

class InstanceProviderTest extends \thamtechunit\di\TestCase
{
    public function testImpliedClassNameProvider()
    {
        $cache = Yii::createObject([
            'class' => 'yii\caching\ArrayCache',
        ]);
        Yii::$container->setSingleton('thamtechunit\di\Cache:::provided', $cache);

        $this->assertSame($cache, Yii::createObject('thamtechunit\di\Cache:::provided'));

        Yii::$container->setSingleton(CacheProvider::class, [
            'class' => CacheProvider::class,
        ]);

        $provider = Yii::$container->get(CacheProvider::class);
        $this->assertSame($cache, $provider->get());
    }

    public function testSpecifiedClassNameProvider()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);
        Yii::$container->setSingleton('myDb', $db);

        $this->assertSame($db, Yii::createObject('myDb'));

        Yii::$container->setSingleton(DbProvider::class, [
            'class' => DbProvider::class,
        ]);

        $provider = Yii::$container->get(DbProvider::class);
        $this->assertSame($db, $provider->get());
    }

    public function testEnsureMatchingType()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);
        Yii::$container->setSingleton('myDb', $db);

        $this->assertSame($db, Yii::createObject('myDb'));

        Yii::$container->setSingleton(DbProvider::class, [
            'class' => DbProvider::class,
            'ensureTypes' => [
                'yii\db\Connection',
            ],
        ]);

        $provider = Yii::$container->get(DbProvider::class);
        $this->assertSame($db, $provider->get());
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureUnmatchedType()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);
        Yii::$container->setSingleton('myDb', $db);

        $this->assertSame($db, Yii::createObject('myDb'));

        Yii::$container->setSingleton(DbProvider::class, [
            'class' => DbProvider::class,
            'ensureTypes' => [
                'yii\caching\Cache', // intentionally wrong type to force exception
            ],
        ]);

        $provider = Yii::$container->get(DbProvider::class);
        $provider->get();
    }
}

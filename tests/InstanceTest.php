<?php

namespace thamtechunit\di;

use thamtech\di\Instance;
use Yii;

class InstanceTest extends \thamtechunit\di\TestCase
{
    public function testEnsureOneClassImplied()
    {
        $db = Instance::ensureAny([
            'dsn' => 'sqlite:path/to/my.db',
        ], \yii\db\Connection::class);

        $this->assertInstanceOf('yii\db\Connection', $db);
    }

    public function testEnsureOneMatchingObject()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);

        $instance = Instance::ensureAny($db, \yii\db\Connection::class);
        $this->assertTrue($db === $instance);
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureOneNonmatchingObject()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);

        $instance = Instance::ensureAny($db, \yii\caching\Cache::class);
    }

    public function testEnsureAnyClassImplied()
    {
        $db = Instance::ensureAny([
            'dsn' => 'sqlite:path/to/my.db',
        ], [
            \yii\db\Connection::class,
            \yii\caching\Cache::class,
        ]);

        $this->assertInstanceOf('yii\db\Connection', $db);
    }

    public function testEnsureAnyMatchingObject()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);

        $instance = Instance::ensureAny($db, [
            \yii\db\Connection::class,
            \yii\caching\Cache::class,
        ]);

        $this->assertTrue($db === $instance);
    }

    public function testEnsureAnyMatchingObjectSecondary()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);

        $instance = Instance::ensureAny($db, [
            \yii\caching\Cache::class,
            \yii\db\Connection::class,
        ]);

        $this->assertTrue($db === $instance);
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureAnyNonmatchingObject()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);

        $instance = Instance::ensureAny($db, [
            \yii\caching\ArrayCache::class,
            \yii\caching\FileCache::class,
        ]);
    }

    public function testEnsureAnyReferenceOnly()
    {
        $db = Instance::ensureAny([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);

        $this->assertInstanceOf('yii\db\Connection', $db);
    }

    public function testEnsureAnyReferenceObjectOnly()
    {
        $db = Yii::createObject([
            'class' => \yii\db\Connection::class,
            'dsn' => 'sqlite:path/to/my.db',
        ]);

        $instance = Instance::ensureAny($db);

        $this->assertTrue($db === $instance);
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureAnyEmptyReferenceEmptyTypes()
    {
        $db = Instance::ensureAny([]);
    }

    public function testEnsureAnyEmptyReference()
    {
        $db = Instance::ensureAny([], 'yii\db\Connection');

        $this->assertInstanceOf('yii\db\Connection', $db);
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureAnyWrongReferenceClass()
    {
        $db = Instance::ensureAny([
            'class' => \yii\caching\FileCache::class,
        ], [
            \yii\db\Connection::class
        ]);
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureEmptyReferenceString()
    {
        $db = Instance::ensureAny('', 'yii\db\Connection');
    }

    public function testEnsureAnyStringReference()
    {
        // 'testdb' application component defined in TestCase.php
        $db = Instance::ensureAny('testdb', [
            \yii\db\Connection::class,
            \yii\caching\Cache::class,
        ]);

        $this->assertInstanceOf('yii\db\Connection', $db);
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureAnyStringReferenceWrongType()
    {
        // 'testdb' application component defined in TestCase.php
        $db = Instance::ensureAny('testdb', [
            \yii\caching\FileCache::class,
            \yii\caching\ArrayCache::class,
        ]);

        $this->assertInstanceOf('yii\db\Connection', $db);
    }

    public function testEnsureAnyStringReferenceOnly()
    {
        // 'testdb' application component defined in TestCase.php
        $db = Instance::ensureAny('testdb');

        $this->assertInstanceOf('yii\db\Connection', $db);
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureAnyBadStringReference()
    {
        $db = Instance::ensureAny('nonexistent', [
            \yii\db\Connection::class,
            \yii\caching\Cache::class,
        ]);
    }

    /**
     * @expectedException yii\base\InvalidConfigException
     */
    public function testEnsureAnyBadStringReferenceOnly()
    {
        $db = Instance::ensureAny('nonexistent');
    }
}

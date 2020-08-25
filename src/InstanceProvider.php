<?php
/*
 * Copyright (C) 2020 Thamtech, LLC
 *
 * This software is copyrighted. No part of this work may be
 * reproduced in whole or in part in any manner without the
 * permission of the Copyright owner, unless specifically authorized
 * by a license obtained from the Copyright owner.
**/

namespace thamtech\di;

use thamtech\di\Instance;
use yii\base\BaseObject;
use Yii;

/**
 * InstanceProvider is a base class for dependency-injection providers.
 *
 * Establish a child of InstanceProvider as a marker to provide a certain instance
 * from a [[Container]] and define it as a constructor parameter where it
 * should be dependency injected.
 *
 * Either specify [[class]], [[params]], and [[config]] to define what should be
 * provided, or rely on the default of looking for an instance in a
 * [[Container]] based on the implementation's class name. See [[class]] for the
 * default behavior.
 *
 * For example:
 *
 * 1. Implement a concrete child class of `InstanceProvider`, such as a `MyCacheProvider`.
 *
 * ```php
 * namespace some\package;
 * class MyCacheProvider extends common\components\di\InstanceProvider {}
 * ```
 *
 * 2. Define a `MyCacheProvider` constructor parameter on the class you want
 *    a cache instance provider injected.
 *
 * ```php
 * namespace some\package;
 * class MyClass
 * {
 *     private $myCache;
 *
 *     function __construct(MyCacheProvider $cacheProvider, $config = [])
 *     {
 *         parent::__construct($config);
 *         $this->myCache = $cacheProvider->get();
 *     }
 * }
 * ```
 *
 * 3. Define a 'MyCache' definition or singleton in the application container.
 *
 * ```php
 * 'container' => [
 *     'singletons' => [
 *         'some\package\MyCache:::provided' => [
 *             'class' => 'yii\caching\DbCache',
 *         ],
 *     ],
 * ],
 * ```
 *
 * If you do not specify a [[class]] name in your provider, the default is to
 * take the full classname of your provider (such as `some\package\MyCacheProvider`),
 * strip off the `Provider` suffix, and append a `:::provided` suffix. So the
 * `some\package\MyCacheProvider` will by default ask the container to get
 * a definition or singleton named `some\package\MyCache:::provided`.
 *
 * The `:::provided` suffix is to prevent the definition from conflicting with
 * a potentially real class named like `some\package\MyCache`. However, if
 * there is such a real class and you want to use it as the provided value,
 * you can set [[class]] to `some\package\MyCache` explicitly.
 *
 * @author Tyler Ham <tyler@thamtech.com>
 */
abstract class InstanceProvider extends BaseObject implements Provider
{
    /**
     * @var Container the dependency injection (DI) container used by [[get()]].
     * `Yii::$container` is used by default if no container is specified here.
     */
    public $container;

    /**
     * @var string the class name or alias name (e.g. `foo`). By default, this
     * will be set to the provider instance's class name without the 'Provider'
     * suffix and with a `:::provided` suffix appended.
     *
     * For example, a `some\package\MyInstanceProvider` implementation will
     * have $class set to `some\package\MyInstance:::provided` by default.
     */
    public $class;

    /**
     * @var array a list of constructor parameter values. The parameters should be provided in the order
     * they appear in the constructor declaration. If you want to skip some parameters, you should index the remaining
     * ones with the integers that represent their positions in the constructor parameter list.
     */
    public $params = [];

    /**
     * @var array a list of name-value pairs that will be used to initialize theobject properties.
     */
    public $config = [];

    /**
     * @var null|array If specified, the value returned by [[get()]] will be
     * ensured to be one of the listed class/interface names.
     */
    public $ensureTypes = null;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        // configure the default class property
        if (!isset($this->class)) {
            $this->class = static::class;

            // remove 'Provider' suffix
            if (preg_match('/^(.+)Provider$/', $this->class, $matches)) {
                $this->class = $matches[1];
            }

            // append ':::provided' suffix
            $this->class .= ':::provided';
        }
    }

    /**
     * Provide the configured instance.
     *
     * @return object an instance of the configured class
     */
    public function get()
    {
        $container = isset($this->container) ? $this->container : Yii::$container;
        $value = $container->get($this->class, $this->params, $this->config);
        return Instance::ensureAny($value, $this->ensureTypes, $container);
    }
}

<?php
/*
 * Copyright (C) 2019-2020 Thamtech, LLC
 *
 * This software is copyrighted. No part of this work may be
 * reproduced in whole or in part in any manner without the
 * permission of the Copyright owner, unless specifically authorized
 * by a license obtained from the Copyright owner.
**/

namespace thamtech\di;

use Yii;
use yii\base\InvalidConfigException;
use yii\di\Container;
use yii\di\Instance as BaseInstance;
use yii\helpers\Inflector;

/**
 * An extension of [[yii\di\Instance]] that adds an [[ensureAny]] method.
 *
 * @author Tyler Ham <tyler@thamtech.com>
 */
class Instance extends BaseInstance
{
    /**
     * Resolves the specified reference into the actual object and makes sure it is of one of the specified types.
     *
     * The reference may be specified as a string or an Instance object. If the former,
     * it will be treated as a component ID, a class/interface name or an alias, depending on the container type.
     *
     * If you do not specify a container, the method will first try `Yii::$app` followed by `Yii::$container`.
     *
     * For example,
     *
     * ```php
     * use yii\db\Connection;
     *
     * // returns Yii::$app->db
     * $db = Instance::ensureAny('db', [Connection::class, \yii\redis\Connection::class]);
     * // returns an instance of Connection using the given configuration
     * //   the first type listed is used as the 'class' parameter when it isn't specified
     * //   in the reference array
     * $db = Instance::ensureAny(['dsn' => 'sqlite:path/to/my.db'], [Connection::class, \yii\redis\Connection::class]);
     * ```
     *
     * @param object|string|array|static $reference an object or a reference to the desired object.
     * You may specify a reference in terms of a component ID or an Instance object.
     * Starting from version 2.0.2, you may also pass in a configuration array for creating the object.
     * If the "class" value is not specified in the configuration array, it will use the value of `$type`.
     * @param string[] $type the class/interface names to be checked. If null, type check will not be performed.
     * @param ServiceLocator|Container $container the container. This will be passed to [[get()]].
     * @return object the object referenced by the Instance, or `$reference` itself if it is an object.
     * @throws InvalidConfigException if the reference is invalid
     */
    public static function ensureAny($reference, $types = null, $container = null)
    {
        if (empty($types)) {
            $types = [];
        }
        if (is_scalar($types)) {
            $types = [$types];
        }

        if (is_array($reference) && (!empty($reference) || !empty($types))) {
            $class = isset($reference['class']) ? $reference['class'] : $types[0];
            if (!$container instanceof Container) {
                $container = Yii::$container;
            }
            unset($reference['class']);
            $component = $container->get($class, [], $reference);
            if (empty($types)) {
                return $component;
            }
            foreach ($types as $type) {
                if ($component instanceof $type) {
                    return $component;
                }
            }

            throw new InvalidConfigException('Invalid data type: ' . $class . '. One of ' . Inflector::sentence($types, ' or ') . ' is expected.');
        } elseif (empty($reference)) {
            throw new InvalidConfigException('The required component is not specified.');
        }

        if (is_string($reference)) {
            $reference = new static($reference);
        } elseif (empty($types)) {
            return $reference;
        } else {
            foreach ($types as $type) {
                if ($reference instanceof $type) {
                    return $reference;
                }
            }
        }

        if ($reference instanceof self) {
            try {
                $component = $reference->get($container);
            } catch (\ReflectionException $e) {
                throw new InvalidConfigException('Failed to instantiate component or class "' . $reference->id . '".', 0, $e);
            }
            if (empty($types)) {
                return $component;
            }
            foreach ($types as $type) {
                if ($component instanceof $type) {
                    return $component;
                }
            }

            throw new InvalidConfigException('"' . $reference->id . '" refers to a ' . get_class($component) . " component. One of " . Inflector::sentence($types, 'or') . " is expected.");
        }

        $valueType = is_object($reference) ? get_class($reference) : gettype($reference);
        throw new InvalidConfigException("Invalid data type: $valueType. One of " . Inflector::sentence($types, ' or ') . " is expected.");
    }
}

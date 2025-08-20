<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

//declare(strict_types=1);

namespace DecodeLabs\Veneer;

use DecodeLabs\Exceptional;

/**
 * @phpstan-require-implements Proxy
 */
trait ProxyTrait
{
    protected static ?object $_veneerInstance = null;

    public static function _setVeneerInstance(
        object $instance
    ): void {
        self::$_veneerInstance = $instance;
    }

    public static function _getVeneerInstance(): ?object
    {
        return self::$_veneerInstance;
    }

    public static function __callStatic(
        string $name,
        array $args
    ): mixed {
        if (!self::$_veneerInstance) {
            throw Exceptional::Runtime(
                message: 'No target object has been bound in ' . $name . ' proxy'
            );
        }

        return self::$_veneerInstance->{$name}(...$args);
    }
}

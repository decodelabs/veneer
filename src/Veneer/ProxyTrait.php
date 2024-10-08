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
    public static ?object $instance = null;

    /**
     * Set Veneer Proxy target instance
     */
    public static function setVeneerProxyTargetInstance(
        object $instance
    ): void {
        self::$instance = $instance;
    }

    /**
     * Get Veneer Proxy target instance
     */
    public static function getVeneerProxyTargetInstance(): ?object
    {
        return self::$instance;
    }

    /**
     * Passthrough all static calls to instance
     */
    public static function __callStatic(
        string $name,
        array $args
    ): mixed {
        if (!self::$instance) {
            throw Exceptional::Runtime(
                'No target object has been bound in ' . $name . ' proxy'
            );
        }

        return self::$instance->{$name}(...$args);
    }
}

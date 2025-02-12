<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

interface Proxy
{
    public static function _setVeneerInstance(
        object $instance
    ): void;

    public static function _getVeneerInstance(): ?object;

    /**
     * @param array<mixed> $args
     */
    public static function __callStatic(
        string $name,
        array $args
    ): mixed;
}

<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

interface Proxy
{
    public static function setVeneerProxyTargetInstance(object $instance): void;
    public static function getVeneerProxyTargetInstance(): ?object;
    public static function __callStatic(string $name, array $args);
}
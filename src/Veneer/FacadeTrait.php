<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

trait FacadeTrait
{
    public static $instance;

    /**
     * Passthrough all static calls to instance
     */
    public static function __callStatic(string $name, array $args)
    {
        if (!self::$instance) {
            Glitch::ERuntime('No target object has been bound in '.$name.' facade');
        }

        return (self::$instance)->{$name}(...$args);
    }
}

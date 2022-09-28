<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Veneer\Manager;

use Psr\Container\ContainerInterface;

final class Veneer
{
    private static ?Manager $defaultManager = null;

    /**
     * Set default manager
     */
    public static function setDefaultManager(Manager $manager): void
    {
        self::$defaultManager = $manager;
    }

    /**
     * Get default manager
     */
    public static function getDefaultManager(): Manager
    {
        if (self::$defaultManager === null) {
            self::$defaultManager = new Manager();
        }

        return self::$defaultManager;
    }



    /**
     * Register provider
     *
     * @phpstan-param class-string $providerClass
     * @phpstan-param class-string $proxyClass
     */
    public static function register(
        string $providerClass,
        string $proxyClass
    ): void {
        $manager = self::getDefaultManager();

        if (!$manager->has($proxyClass)) {
            $manager->bind($providerClass, $proxyClass);
        }
    }


    /**
     * Set PSR11 container
     */
    public static function setContainer(?ContainerInterface $container): void
    {
        self::getDefaultManager()->setContainer($container);
    }

    /**
     * Get PSR11 container
     */
    public static function getContainer(): ?ContainerInterface
    {
        return self::getDefaultManager()->getContainer();
    }


    public static function ensurePlugin(
        object $target,
        string $name
    ): void {
        if (isset($target->{$name})) {
            return;
        }

        $manager = self::getDefaultManager();
        $manager->ensurePlugin($target, $name);
    }


    /**
     * Should cache bindings in tmp
     */
    public static function shouldCacheBindings(): bool
    {
        if (defined('__PHPSTAN_RUNNING__')) {
            return true;
        }

        return false;
    }
}

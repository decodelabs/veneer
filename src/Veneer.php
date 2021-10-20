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
    /**
     * @var Manager|null
     */
    private static $defaultManager;

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
     */
    public static function register(string $providerClass, string $proxyClass): void
    {
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

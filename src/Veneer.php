<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs;

use DecodeLabs\Veneer\Manager;
use DecodeLabs\Veneer\Manager\Aliasing as AliasingManager;

final class Veneer
{
    protected static $defaultManager;

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
        if (!self::$defaultManager) {
            self::$defaultManager = new AliasingManager();
        }

        return self::$defaultManager;
    }



    /**
     * Register provider
     */
    public static function register(string $providerClass, string ...$proxyClasses): void
    {
        $manager = self::getDefaultManager();
        $name = $providerClass;

        if (!empty($proxyClasses)) {
            $parts = explode('\\', $proxyClasses[0]);
            $name = array_pop($parts);
        }

        if (!$manager->has($providerClass)) {
            $manager->bind($name, $providerClass);
        }

        foreach ($proxyClasses as $className) {
            $manager->load($name, $className);
        }
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

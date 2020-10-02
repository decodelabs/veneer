<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Veneer\Listener;
use DecodeLabs\Veneer\Listener\Autoload;

final class Register
{
    public static $instance;

    /**
     * Register global listener instance
     */
    public static function setGlobalListener(Listener $listener): void
    {
        if (self::$instance) {
            self::$instance->stopListening();

            foreach (self::$instance->getManagers() as $manager) {
                self::$instance->unregisterManager($manager);
                $listener->registerManager($manager);
            }

            $listener->blacklistNamespaces(...self::$instance->getBlacklistedNamespaces());
        }

        self::$instance = $listener;
        $listener->startListening();
    }

    /**
     * Get current global instance
     */
    public static function getGlobalListener(): Listener
    {
        if (!self::$instance) {
            self::setGlobalListener(new Autoload());
        }

        return self::$instance;
    }
}

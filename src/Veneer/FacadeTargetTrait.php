<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Veneer\Register;
use Psr\Container\ContainerInterface;

trait FacadeTargetTrait
{
    /**
     * Register as Veneer facade
     */
    public static function registerFacade(string ...$autoBind): void
    {
        $class = get_called_class();

        if (!defined($class.'::FACADE')) {
            throw Glitch::ESetup('Facade target '.$class.' has not defined the facade name', null, $class);
        }

        $name = $class::FACADE;
        $manager = Register::getGlobalListener()->getDefaultManager();

        if (!$manager->hasFacade($name)) {
            self::bindFacade($manager, $name, $class);
        }

        foreach ($autoBind as $className) {
            $manager->loadManual($name, $className);
        }
    }

    /**
     * Bind class as facade
     */
    public static function bindFacade(Manager $manager, string $name, string $class): void
    {
        $manager->bindGlobalFacade($name, $class);
    }



    /**
     * Stub to get empty plugin list to avoid broken targets
     */
    public function getFacadePluginNames(): array
    {
        return [];
    }

    /**
     * Stub to avoid broken targets
     */
    public function loadFacadePlugin(string $name): FacadePlugin
    {
        throw Glitch::EImplementation('Facade target has not implemented a plugin loader', null, $this);
    }
}

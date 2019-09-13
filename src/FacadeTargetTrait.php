<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

trait FacadeTargetTrait
{
    /**
     * Register as Veneer facade
     */
    public static function registerFacade(?ContainerInterface $container=null, ?Manager $veneer=null): ?Manager
    {
        return self::registerGlobalFacade($container, $veneer);
    }

    /**
     * Register as global facade
     */
    public static function registerGlobalFacade(?ContainerInterface $container=null, ?Manager $veneer=null): ?Manager
    {
        return self::prepareFacadeRegistration($container, $veneer, function ($veneer, $name, $class) {
            $veneer->bindGlobalFacade($name, $class);
        });
    }

    /**
     * Register as root facade
     */
    public static function registerRootFacade(?ContainerInterface $container=null, ?Manager $veneer=null): ?Manager
    {
        return self::prepareFacadeRegistration($container, $veneer, function ($veneer, $name, $class) {
            $veneer->bindRootFacade($name, $class);
        });
    }

    /**
     * Register as root facade
     */
    public static function registerLocalFacade(?ContainerInterface $container=null, ?Manager $veneer=null): ?Manager
    {
        return self::prepareFacadeRegistration($container, $veneer, function ($veneer, $name, $class) {
            $veneer->bindLocalFacade($name, $class);
        });
    }

    /**
     * Prepare for facade registration
     */
    private static function prepareFacadeRegistration(?ContainerInterface $container=null, ?Manager $veneer=null, callable $callback): ?Manager
    {
        $class = get_called_class();

        if (!defined($class.'::FACADE')) {
            throw \Glitch::ESetup('Facade target '.$class.' has not defined the facade name', null, $class);
        }

        $name = $class::FACADE;

        if (!$veneer) {
            if ($default = Manager::getDefault()) {
                $veneer = $default;
            } elseif ($container && $container->has(Manager::class)) {
                $veneer = $container->get(Manager::class);
            } else {
                Manager::setDefault($veneer = new Manager($container));

                if ($container && method_exists($container, 'bind')) {
                    $container->bind(Manager::class, $veneer);
                }
            }
        }

        if (!$veneer->hasFacade($name)) {
            $callback($veneer, $name, $class);
        }

        return $veneer;
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
        throw \Glitch::EImplementation('Facade target has not implemented a plugin loader', null, $this);
    }
}

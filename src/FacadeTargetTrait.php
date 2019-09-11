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
        $class = get_called_class();

        if (!defined($class.'::FACADE')) {
            throw \Glitch::ESetup('Facade target '.$class.' has not defined the facade name', null, $class);
        }

        $name = $class::FACADE;

        if (class_exists($name)) {
            return null;
        }

        if (!$veneer) {
            if ($container && $container->has(Manager::class)) {
                $veneer = $container->get(Manager::class);
            } else {
                $veneer = new Manager($container);

                if ($container && method_exists($container, 'bind')) {
                    $container->bind(Manager::class, $veneer);
                }
            }
        }

        if (!$veneer->hasFacade($name)) {
            $veneer->bindGlobalFacade($name, $class);
        }

        return $veneer;
    }
}

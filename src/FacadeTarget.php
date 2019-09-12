<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

interface FacadeTarget
{
    public static function registerFacade(?ContainerInterface $container=null, ?Manager $veneer=null): ?Manager;
    public static function registerGlobalFacade(?ContainerInterface $container=null, ?Manager $veneer=null): ?Manager;
    public static function registerRootFacade(?ContainerInterface $container=null, ?Manager $veneer=null): ?Manager;
    public static function registerLocalFacade(?ContainerInterface $container=null, ?Manager $veneer=null): ?Manager;

    public function getFacadePluginNames(): array;
    public function loadFacadePlugin(string $name): FacadePlugin;
}

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
    public static function registerFacade(): void;
    public static function bindFacade(Manager $manager, string $name, string $class): void;

    public function getFacadePluginNames(): array;
    public function loadFacadePlugin(string $name): FacadePlugin;
}

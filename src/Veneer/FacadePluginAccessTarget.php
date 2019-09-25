<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

interface FacadePluginAccessTarget extends FacadeTarget
{
    public function __get(string $name): FacadePlugin;
    public function cacheLoadedFacadePlugin(string $name, FacadePlugin $plugin): void;
}

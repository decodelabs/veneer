<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Veneer\Register;
use Psr\Container\ContainerInterface;

trait FacadePluginAccessTargetTrait
{
    protected $plugins = [];

    /**
     * Load local object plugin
     */
    public function __get(string $name): FacadePlugin
    {
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = $this->loadFacadePlugin($name);
        }

        return $this->plugins[$name];
    }

    public function cacheLoadedFacadePlugin(string $name, FacadePlugin $plugin): void
    {
        $this->plugins[$name] = $plugin;
    }
}

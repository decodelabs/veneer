<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

use DecodeLabs\Veneer\Plugin;

trait AccessTargetTrait
{
    /**
     * @var array<string, Plugin>
     */
    protected array $plugins = [];

    /**
     * Load local object plugin
     */
    public function __get(string $name): Plugin
    {
        if (!isset($this->plugins[$name])) {
            $this->plugins[$name] = $this->loadVeneerPlugin($name);
        }

        return $this->plugins[$name];
    }

    public function cacheLoadedVeneerPlugin(
        string $name,
        Plugin $plugin
    ): void {
        $this->plugins[$name] = $plugin;
    }
}

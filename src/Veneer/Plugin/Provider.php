<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

use DecodeLabs\Veneer\Plugin;

interface Provider
{
    /**
     * @return array<string>
     */
    public function getVeneerPluginNames(): array;

    public function loadVeneerPlugin(string $name): Plugin;
}

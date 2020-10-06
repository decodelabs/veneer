<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

use DecodeLabs\Veneer\Plugin;

interface AccessTarget extends Provider
{
    public function __get(string $name): Plugin;
    public function cacheLoadedVeneerPlugin(string $name, Plugin $plugin): void;
}

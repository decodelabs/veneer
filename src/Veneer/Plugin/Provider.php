<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer\Plugin;

use DecodeLabs\Veneer\Plugin;

interface Provider
{
    public function getVeneerPluginNames(): array;
    public function loadVeneerPlugin(string $name): Plugin;
}

<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

use DecodeLabs\Exceptional;
use DecodeLabs\Veneer\Plugin;

trait ProviderTrait
{
    /**
     * Stub to get empty plugin list to avoid broken targets
     */
    public function getVeneerPluginNames(): array
    {
        return [];
    }

    /**
     * Stub to avoid broken targets
     */
    public function loadVeneerPlugin(string $name): Plugin
    {
        throw Exceptional::Implementation(
            'Veneer provider has not implemented a plugin loader',
            null,
            $this
        );
    }
}

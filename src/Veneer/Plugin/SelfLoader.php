<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

interface SelfLoader
{
    public static function loadAsVeneerPlugin(object $instance): static;
}

<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Veneer\Manager;

Manager::getGlobalManager()->register(
    Manager::class,
    Veneer::class
);

<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

interface ContainerProvider
{
    public ?ContainerInterface $container { get; }
}

<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

interface Manager
{
    public function setContainer(?ContainerInterface $container): void;
    public function getContainer(): ?ContainerInterface;

    public function bind(string $name, string $key): void;
    public function has(string $name): bool;
    public function hasPlugin(string $bindName, string $pluginName): bool;
    public function prepare(string $name): ?Binding;
    public function getBindings(): array;

    public function load(string $name, string $className): bool;
}

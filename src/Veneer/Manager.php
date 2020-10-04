<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

interface Manager
{
    public function bind(string $name, string $key): Manager;
    public function has(string $name): bool;
    public function hasPlugin(string $bindName, string $pluginName): bool;
    public function prepare(string $name): ?Binding;
    public function getBindings(): array;

    public function load(string $name, string $className): bool;
}

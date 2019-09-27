<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

interface Manager
{
    public function bindGlobalFacade(string $name, string $key): Manager;
    public function bindLocalFacade(string $name, string $key): Manager;
    public function bindRootFacade(string $name, string $key): Manager;
    public function bindNamespaceFacade(string $name, string $key, string $namespace): Manager;
    public function hasFacade(string $name): bool;
    public function prepareFacade(string $name): ?Binding;

    public function load(string $name, ?string $namespace): bool;
    public function loadManual(string $name, string $className): bool;
}

<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

interface Listener
{
    public function startListening(): void;
    public function stopListening(): void;
    public function isListening(): bool;

    public function registerManager(Manager $manager): void;
    public function unregisterManager(Manager $manager): void;
    public function hasManager(Manager $manager): bool;
    public function getManagers(): array;
    public function getDefaultManager(): Manager;

    public function blacklistNamespaces(string ...$namespaces): Listener;
    public function isNamespaceBlacklisted(string $namespace): bool;
    public function getBlacklistedNamespaces(): array;
}

<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Veneer\FacadeTarget;
use DecodeLabs\Veneer\FacadeTargetTrait;
use DecodeLabs\Veneer\FacadePlugin;

use DecodeLabs\Veneer\Register;
use DecodeLabs\Veneer\Listener;
use DecodeLabs\Veneer\Listener\Autoload;

use DecodeLabs\Exceptional;

class Context implements FacadeTarget
{
    use FacadeTargetTrait;

    const FACADE = 'Veneer';

    /**
     * Get global listener
     */
    public function getGlobalListener(): Listener
    {
        return Register::getGlobalListener();
    }

    /**
     * Register manager instance
     */
    public function registerManager(Manager $manager): Context
    {
        Register::getGlobalListener()->registerManager($manager);
        return $this;
    }

    /**
     * Unregister manager instance
     */
    public function unregisterManager(Manager $manager): Context
    {
        Register::getGlobalListener()->unregisterManager($manager);
        return $this;
    }

    /**
     * Is manager registered
     */
    public function hasManager(Manager $manager): bool
    {
        return Register::getGlobalListener()->hasManager($manager);
    }

    /**
     * Get all registered managers
     */
    public function getManagers(): array
    {
        return Register::getGlobalListener()->getManagers();
    }

    /**
     * Add namespace to the blacklist
     */
    public function blacklistNamespaces(string ...$namespaces): Context
    {
        Register::getGlobalListener()->blacklistNamespaces(...$namespaces);
        return $this;
    }

    /**
     * Check if namespace has been blacklisted
     */
    public function isNamespaceBlacklisted(string $namespace): bool
    {
        return Register::getGlobalListener()->isNamespaceBlacklisted($namespace);
    }

    /**
     * Get list of blacklisted namespaces
     */
    public function getBlacklistedNamespaces(): array
    {
        return Register::getGlobalListener()->getBlacklistedNamespaces();
    }

    /**
     * Add namespace to the whitelist
     */
    public function whitelistNamespaces(string ...$namespaces): Context
    {
        Register::getGlobalListener()->whitelistNamespaces(...$namespaces);
        return $this;
    }

    /**
     * Check if namespace has been whitelisted
     */
    public function isNamespaceWhitelisted(string $namespace): bool
    {
        return Register::getGlobalListener()->isNamespaceWhitelisted($namespace);
    }

    /**
     * Get list of whitelisted namespaces
     */
    public function getWhitelistedNamespaces(): array
    {
        return Register::getGlobalListener()->getWhitelistedNamespaces();
    }

    /**
     * Check if namespace has been blacklisted or whitelisted
     */
    public function isNamespaceListed(string $namespace): bool
    {
        return Register::getGlobalListener()->isNamespaceListed($namespace);
    }

    /**
     * Get all listed namespaces
     */
    public function getListedNamespaces(): array
    {
        return Register::getGlobalListener()->getListedNamespaces();
    }

    /**
     * Get active target object for Facade
     */
    public function get(string $name)
    {
        foreach ($this->getManagers() as $manager) {
            if ($facade = $manager->prepareFacade($name)) {
                return ($facade->getTarget($name))::$instance;
            }
        }

        throw Exceptional::InvalidArgument(
            $name.' has not been bound as a Facade'
        );
    }
}
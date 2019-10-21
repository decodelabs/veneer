<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Veneer\Manager\Aliasing;
use Psr\Container\ContainerInterface;

trait ListenerTrait
{
    protected $managers = [];
    protected $namespaces = [];

    /**
     * Register manager instance
     */
    public function registerManager(Manager $manager): void
    {
        $id = spl_object_id($manager);
        $this->managers[$id] = $manager;
    }

    /**
     * Unregister manager instance
     */
    public function unregisterManager(Manager $manager): void
    {
        $id = spl_object_id($manager);
        unset($this->managers[$id]);
    }

    /**
     * Is manager registered
     */
    public function hasManager(Manager $manager): bool
    {
        $id = spl_object_id($manager);
        return isset($this->managers[$id]);
    }

    /**
     * Get list of registered managers
     */
    public function getManagers(): array
    {
        return $this->managers;
    }

    /**
     * Get default manager
     */
    public function getDefaultManager(): Manager
    {
        if (empty($this->managers)) {
            $manager = new Aliasing();
            $this->registerManager($manager);
            return $manager;
        } else {
            foreach ($this->managers as $manager) {
                return $manager;
            }
        }
    }



    /**
     * Add namespace to the blacklist
     */
    public function blacklistNamespaces(string ...$namespaces): Listener
    {
        foreach ($namespaces as $namespace) {
            $namespace = ltrim($namespace, '\\');
            $this->namespaces[$namespace] = false;
        }

        return $this;
    }

    /**
     * Check if namespace has been blacklisted
     */
    public function isNamespaceBlacklisted(string $namespace): bool
    {
        return isset($this->namespaces[$namespace]) && $this->namespaces[$namespace] === false;
    }

    /**
     * Get list of blacklisted namespaces
     */
    public function getBlacklistedNamespaces(): array
    {
        $output = [];

        foreach ($this->namespaces as $namespace => $accept) {
            if (!$accept) {
                $output[] = $namespace;
            }
        }

        return $output;
    }

    /**
     * Add namespace to the whitelist
     */
    public function whitelistNamespaces(string ...$namespaces): Listener
    {
        foreach ($namespaces as $namespace) {
            $namespace = ltrim($namespace, '\\');
            $this->namespaces[$namespace] = true;
        }

        return $this;
    }

    /**
     * Check if namespace has been whitelisted
     */
    public function isNamespaceWhitelisted(string $namespace): bool
    {
        return isset($this->namespaces[$namespace]) && $this->namespaces[$namespace] === true;
    }

    /**
     * Get list of whitelisted namespaces
     */
    public function getWhitelistedNamespaces(): array
    {
        $output = [];

        foreach ($this->namespaces as $namespace => $accept) {
            if ($accept) {
                $output[] = $namespace;
            }
        }

        return $output;
    }

    /**
     * Check if namespace has been blacklisted or whitelisted
     */
    public function isNamespaceListed(string $namespace): bool
    {
        return isset($this->namespaces[$namespace]);
    }

    /**
     * Get all listed namespaces
     */
    public function getListedNamespaces(): array
    {
        return $this->namespaces;
    }
}

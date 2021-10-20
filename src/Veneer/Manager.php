<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

class Manager
{
    /**
     * @var array<string, Binding>
     */
    protected $bindings = [];

    /**
     * @var ContainerInterface|null
     */
    protected $container;

    /**
     * Init with container and loader
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->setContainer($container);
    }


    /**
     * Set PSR11 container
     */
    public function setContainer(?ContainerInterface $container): void
    {
        $this->container = $container;
    }

    /**
     * Get PSR11 container
     */
    public function getContainer(): ?ContainerInterface
    {
        return $this->container;
    }


    /**
     * Add alias that can be used from root namespace
     */
    public function bind(string $providerClass, string $proxyClass): bool
    {
        if (class_exists($proxyClass, false)) {
            return false;
        }

        $binding = new Binding($providerClass, $proxyClass);
        $this->bindings[$binding->getProxyClass()] = $binding;

        if (!$binding->hasInstance()) {
            $binding->bindInstance($this->container);
        }

        $bindingClass = get_class($binding->getTarget());
        class_alias($bindingClass, $proxyClass);

        return true;
    }

    /**
     * Has class been bound?
     */
    public function has(string $proxyClass): bool
    {
        return isset($this->bindings[$proxyClass]);
    }

    /**
     * Has class been bound with plugin?
     */
    public function hasPlugin(string $proxyClass, string $pluginName): bool
    {
        if (!$binding = ($this->bindings[$proxyClass] ?? null)) {
            return false;
        }

        if (!$binding->hasInstance()) {
            $binding->bindInstance($this->container);
        }

        return $binding->hasPlugin($pluginName);
    }

    /**
     * Get all bindings
     *
     * @return array<string, Binding>
     */
    public function getBindings(): array
    {
        $output = [];

        foreach ($this->bindings as $name => $binding) {
            if (!$binding->hasInstance()) {
                $binding->bindInstance($this->container);
            }

            $output[$name] = $binding;
        }

        return $output;
    }
}

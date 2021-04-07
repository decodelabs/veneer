<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

trait ManagerTrait
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
    public function bind(string $name, string $key): void
    {
        $binding = new Binding($name, $key);
        $this->bindings[$binding->getName()] = $binding;
    }

    /**
     * Has class been bound?
     */
    public function has(string $name): bool
    {
        return isset($this->bindings[$name]);
    }

    /**
     * Has class been bound with plugin?
     */
    public function hasPlugin(string $bindName, string $pluginName): bool
    {
        if (!$binding = ($this->bindings[$bindName] ?? null)) {
            return false;
        }

        if (!$binding->hasInstance()) {
            $binding->bindInstance($this->container);
        }

        return $binding->hasPlugin($pluginName);
    }

    /**
     * Prepare binding and ensure instance has been bound
     */
    public function prepare(string $name): ?Binding
    {
        if (!$binding = ($this->bindings[$name] ?? null)) {
            return null;
        }

        if (!$binding->hasInstance()) {
            $binding->bindInstance($this->container);
        }

        return $binding;
    }

    /**
     * Get all bindings
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

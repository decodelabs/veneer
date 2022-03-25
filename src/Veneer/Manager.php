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
     * @var bool
     */
    protected $deferrals = true;

    /**
     * Init with container and loader
     */
    public function __construct(?ContainerInterface $container = null)
    {
        $this->setContainer($container);
        spl_autoload_register([$this, 'handleAutoload']);
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
     * Handle autoload
     */
    public function handleAutoload(string $class): void
    {
        if (!isset($this->bindings[$class])) {
            return;
        }

        $this->bindProxy($this->bindings[$class]);
    }


    /**
     * Set deferral resolution on or off
     */
    public function setDeferrals(bool $flag): void
    {
        $this->deferrals = $flag;
    }


    /**
     * Add alias that can be used from root namespace
     *
     * @phpstan-param class-string $providerClass
     * @phpstan-param class-string $proxyClass
     */
    public function bind(
        string $providerClass,
        string $proxyClass
    ): bool {
        if (class_exists($proxyClass, false)) {
            return false;
        }

        $binding = new Binding($providerClass, $proxyClass);
        $this->bindings[$binding->getProxyClass()] = $binding;

        if (!$binding->isLazyLoader()) {
            $this->bindProxy($binding);
        }

        return true;
    }

    /**
     * Bind proxy
     */
    protected function bindProxy(Binding $binding): void
    {
        if (!$binding->hasInstance()) {
            $binding->bindInstance($this->container);
        }

        $bindingClass = get_class($binding->getTarget());
        class_alias($bindingClass, $binding->getProxyClass());

        if (
            $binding->isDeferred() &&
            $this->deferrals
        ) {
            $binding->resolveDeferral();
        }
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

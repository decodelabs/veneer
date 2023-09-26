<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use DecodeLabs\Exceptional;
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;
use Psr\Container\ContainerInterface;

class Manager
{
    /**
     * @var array<string, Binding>
     */
    protected array $bindings = [];

    protected ?ContainerInterface $container = null;
    protected bool $deferrals = true;

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
     * @param class-string $providerClass
     * @param class-string $proxyClass
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
    public function hasPlugin(
        string $proxyClass,
        string $pluginName
    ): bool {
        if (!$binding = ($this->bindings[$proxyClass] ?? null)) {
            return false;
        }

        if (!$binding->hasInstance()) {
            $binding->bindInstance($this->container);
        }

        return $binding->hasPlugin($pluginName);
    }

    /**
     * Ensure instance has plugin
     */
    public function ensurePlugin(
        object $instance,
        string $name
    ): void {
        if (isset($instance->{$name})) {
            return;
        }

        $binding = $this->getBindingForInstance($instance);
        $names = $binding->getPluginNames();

        if (!in_array($name, $names)) {
            throw Exceptional::Runtime(get_class($instance) . ' does not have plugin ' . $name);
        }

        $target = $binding->getTarget();
        $plugin = $target::$$name;

        if ($plugin instanceof PluginWrapper) {
            $plugin = $plugin->getVeneerPlugin();
        }

        $instance->{$name} = $plugin;
    }

    /**
     * Replace instance of plugin
     */
    public function replacePlugin(
        object $instance,
        string $name,
        mixed $plugin
    ): void {
        $binding = $this->getBindingForInstance($instance);
        $names = $binding->getPluginNames();

        if (!in_array($name, $names)) {
            throw Exceptional::Runtime(get_class($instance) . ' does not have plugin ' . $name);
        }

        $instance->{$name} = $plugin;
        $target = $binding->getTarget();
        $target::$$name = $plugin;
    }

    /**
     * Find binding for class of instance
     */
    protected function getBindingForInstance(object $instance): Binding
    {
        $class = get_class($instance);

        foreach ($this->bindings as $binding) {
            if ($binding->getProviderClass() !== $class) {
                continue;
            }

            return $binding;
        }

        throw Exceptional::Runtime('Unable to find binding for ' . get_class($instance));
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

    /**
     * Get single binding
     */
    public function getBinding(string $name): ?Binding
    {
        if (!isset($this->bindings[$name])) {
            return null;
        }

        $binding = $this->bindings[$name];

        if (!$binding->hasInstance()) {
            $binding->bindInstance($this->container);
        }

        return $binding;
    }
}

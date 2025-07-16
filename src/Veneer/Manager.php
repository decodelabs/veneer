<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use DecodeLabs\Exceptional;
use DecodeLabs\Pandora\Container as PandoraContainer;
use DecodeLabs\Veneer\Stub\Generator as StubGenerator;
use Psr\Container\ContainerInterface;

class Manager implements ContainerProvider
{
    private static ?Manager $instance = null;

    /**
     * @var array<string, Binding>
     */
    protected array $bindings = [];

    public ?ContainerInterface $container = null {
        get => $this->container;
        set {
            $this->container = $value;

            if (
                class_exists(PandoraContainer::class) &&
                $this->container instanceof PandoraContainer
            ) {
                foreach ($this->bindings as $binding) {
                    $providerClass = $binding->getProviderClass();

                    if (
                        !$binding->hasInstance() ||
                        $this->container->has($providerClass) ||
                        null === ($instance = ($binding->getProxy())::_getVeneerInstance())
                    ) {
                        continue;
                    }

                    $this->container->bindShared($providerClass, $instance);
                }
            }
        }
    }

    public function setContainer(
        ContainerInterface $container
    ): void {
        $this->container = $container;
    }


    /**
     * Get singleton instance
     */
    public static function getGlobalManager(): Manager
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    /**
     * Init with container and loader
     */
    public function __construct()
    {
        spl_autoload_register($this->mount(...));
    }


    /**
     * Handle autoload
     */
    protected function mount(
        string $class
    ): void {
        if (!isset($this->bindings[$class])) {
            return;
        }

        $binding = $this->bindings[$class];

        if (!$binding->hasInstance()) {
            $binding->mount($this);
        }

        $bindingClass = get_class($binding->getProxy());
        class_alias($bindingClass, $binding->getProxyClass());
    }

    /**
     * Add alias that can be used from root namespace
     *
     * @param class-string $providerClass
     * @param class-string $proxyClass
     */
    public function register(
        string $providerClass,
        string $proxyClass
    ): bool {
        if (class_exists($proxyClass, false)) {
            return false;
        }

        $binding = new Binding($providerClass, $proxyClass);
        $this->bindings[$binding->getProxyClass()] = $binding;

        return true;
    }

    /**
     * Has class been bound?
     */
    public function has(
        string $proxyClass
    ): bool {
        return isset($this->bindings[$proxyClass]);
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

        if (!$pluginAttr = $binding->getPlugin($name)) {
            throw Exceptional::Runtime(get_class($instance) . ' does not have plugin ' . $name);
        }

        $pluginAttr->property->setValue($instance, $plugin);
        $proxy = $binding->getProxy();
        $proxy::$$name = $plugin;
    }

    /**
     * Find binding for class of instance
     */
    protected function getBindingForInstance(
        object $instance
    ): Binding {
        $class = get_class($instance);

        foreach ($this->bindings as $binding) {
            if ($binding->getProviderClass() !== $class) {
                continue;
            }

            return $binding;
        }

        throw Exceptional::Runtime(
            message: 'Unable to find binding for ' . get_class($instance)
        );
    }

    /**
     * Get all bindings
     *
     * @return array<string,Binding>
     */
    public function getBindings(
        bool $mount = false
    ): array {
        if (!$mount) {
            return $this->bindings;
        }

        $output = [];

        foreach ($this->bindings as $name => $binding) {
            if (!$binding->hasInstance()) {
                $binding->mount($this);
            }

            $output[$name] = $binding;
        }

        return $output;
    }

    /**
     * Get single binding
     */
    public function getBinding(
        string $name,
        bool $mount = false
    ): ?Binding {
        if (!isset($this->bindings[$name])) {
            return null;
        }

        $binding = $this->bindings[$name];

        if (
            $mount &&
            !$binding->hasInstance()
        ) {
            $binding->mount($this);
        }

        return $binding;
    }


    /**
     * Create new stub generator
     */
    public function newStubGenerator(
        string $scanDir,
        string $stubDir
    ): StubGenerator {
        return new StubGenerator($scanDir, $stubDir);
    }
}

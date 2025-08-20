<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

//declare(strict_types=1);

namespace DecodeLabs\Veneer;

use DecodeLabs\Exceptional;
use DecodeLabs\Pandora\Container as PandoraContainer;
use DecodeLabs\Slingshot;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;
use DecodeLabs\Veneer\Proxy\ClassGenerator;
use ReflectionClass;

class Binding
{
    /**
     * @var class-string
     */
    protected string $providerClass;

    /**
     * @var class-string
     */
    protected string $proxyClass;

    protected ?Proxy $proxy = null;

    /**
     * @var ?array<string,Plugin>
     */
    protected ?array $plugins = null;


    /**
     * @param class-string $providerClass
     * @param class-string $proxyClass
     */
    public function __construct(
        string $providerClass,
        string $proxyClass
    ) {
        $this->providerClass = $providerClass;
        $this->proxyClass = $proxyClass;
    }


    /**
     * @return class-string
     */
    public function getProviderClass(): string
    {
        return $this->providerClass;
    }

    /**
     * @return class-string
     */
    public function getProxyClass(): string
    {
        return $this->proxyClass;
    }


    /**
     * @return $this
     */
    public function mount(
        ContainerProvider $containerProvider
    ): Binding {
        $instance = null;
        $container = $containerProvider->container;

        // Check container for provider
        if (
            $container &&
            $container->has($this->providerClass)
        ) {
            $instance = $container->get($this->providerClass);
        }

        if ($this->proxyClass === Veneer::class) {
            $instance = Manager::getGlobalManager();
        }

        // Create instance of provider
        if (
            !$instance &&
            (false !== strpos($this->providerClass, '\\')) &&
            class_exists($this->providerClass)
        ) {
            $ref = new ReflectionClass($this->providerClass);
            $deferred = true;

            $instance = $ref->newLazyGhost(function ($instance) use ($containerProvider, $ref) {
                $container = $containerProvider->container;

                // Call constructor
                if ($ref->hasMethod('__construct')) {
                    $method = $ref->getMethod('__construct');
                    $closure = $method->getClosure($instance);
                    $needsSlingshot = false;

                    foreach ($method->getParameters() as $parameter) {
                        if (!$parameter->isOptional()) {
                            $needsSlingshot = true;
                            break;
                        }
                    }

                    if ($needsSlingshot) {
                        if (!class_exists(Slingshot::class)) {
                            throw Exceptional::ComponentUnavailable(
                                message: 'Cannot resolve constructor dependencies without Slingshot'
                            );
                        }

                        // Invoke constructor with Slingshot
                        new Slingshot($container)
                            ->invoke($closure);
                    } else {
                        // Invoke constructor directly
                        $closure();
                    }
                }


                // Apply plugins
                foreach ($this->getPlugins() as $name => $plugin) {
                    if (
                        !$plugin->auto ||
                        !isset($this->proxy::$$name) ||
                        $this->proxy::$$name instanceof PluginWrapper
                    ) {
                        continue;
                    }

                    // Inject proxy into instance
                    $plugin->property->setValue($instance, $this->proxy::$$name);
                }

                // Add instance to container
                $this->bindInstanceInContainer($instance, $containerProvider);
            });
        } else {
            $deferred = false;
        }

        // Check instance
        if (!is_object($instance)) {
            throw Exceptional::Runtime(
                message: 'Could not get instance of ' . $this->providerClass . ' to bind to',
                data: $this
            );
        }


        // Create proxy
        $this->scanPlugins();
        $this->proxy = $this->createProxyClass(get_class($instance));
        $this->proxy::_setVeneerInstance($instance);
        $this->loadPlugins($instance, $containerProvider);

        if (!$deferred) {
            // Add instance to container
            $this->bindInstanceInContainer($instance, $containerProvider);
        }

        return $this;
    }

    protected function bindInstanceInContainer(
        object $instance,
        ContainerProvider $containerProvider
    ): void {
        if (
            !class_exists(PandoraContainer::class) ||
            !($container = $containerProvider->container) instanceof PandoraContainer
        ) {
            return;
        }

        if (!$container->has($this->providerClass)) {
            $container->bind($this->providerClass, $instance);
        }
    }




    public function hasInstance(): bool
    {
        return
            $this->proxy !== null &&
            $this->proxy::_getVeneerInstance() !== null;
    }

    public function getInstance(): ?object
    {
        if ($this->proxy === null) {
            return null;
        }

        return $this->proxy::_getVeneerInstance();
    }


    public function getProxy(): Proxy
    {
        if ($this->proxy === null) {
            throw Exceptional::Runtime(
                message: 'Proxy ' . $this->proxyClass . ' has not been bound to target yet',
                data: $this
            );
        }

        return $this->proxy;
    }



    /**
     * @param class-string $instanceClass
     */
    private function createProxyClass(
        string $instanceClass
    ): Proxy {
        $generator = new ClassGenerator($this, $instanceClass);

        $class = $generator->generate(
            namespace: 'DecodeLabs\\Veneer\\Binding',
        );

        $class .= 'return new \\DecodeLabs\\Veneer\\Binding\\' . $this->proxyClass . '();' . "\n";

        if ($this->shouldCacheBindings()) {
            $hash = md5($class);
            $path = '/tmp/decodelabs/veneer';
            $fileName = $path . '/binding_' . $hash . '.php';

            if (!is_file($fileName)) {
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }

                file_put_contents($fileName, '<?php' . "\n" . $class);
            }

            $output = require $fileName;
        } else {
            $output = eval($class);
        }

        /** @var Proxy $output */
        return $output;
    }

    private function shouldCacheBindings(): bool
    {
        return defined('__PHPSTAN_RUNNING__');
    }




    private function scanPlugins(): void
    {
        $this->plugins = [];

        $ref = new ReflectionClass($this->providerClass);
        $props = $ref->getProperties();

        foreach ($props as $property) {
            $pluginAttr = $property->getAttributes(Plugin::class);

            if (empty($pluginAttr)) {
                continue;
            }

            $plugin = $pluginAttr[0]->newInstance();
            $plugin->property = $property;

            $this->plugins[$plugin->name] = $plugin;
        }
    }


    /**
     * @return array<string,Plugin>
     */
    public function getPlugins(): array
    {
        if ($this->plugins === null) {
            $this->scanPlugins();
        }

        /** @var array<string,Plugin> */
        return $this->plugins;
    }

    /**
     * @return array<string>
     */
    public function getPluginNames(): array
    {
        return array_keys($this->getPlugins());
    }

    public function getPlugin(
        string $name
    ): ?Plugin {
        return $this->getPlugins()[$name] ?? null;
    }

    public function hasPlugin(
        string $name
    ): bool {
        return isset($this->getPlugins()[$name]);
    }



    private function loadPlugins(
        object $instance,
        ContainerProvider $containerProvider
    ): void {
        if ($this->proxy === null) {
            throw Exceptional::Setup(
                message: 'Target binding has not been created'
            );
        }

        $instRef = new ReflectionClass($instance);
        $instanceLazy = $instRef->isUninitializedLazyObject($instance);

        foreach ($this->getPlugins() as $name => $plugin) {
            $this->proxy::$$name = $object = $this->loadPlugin(
                plugin: $plugin,
                instance: $instance,
                instanceLazy: $instanceLazy,
                containerProvider: $containerProvider
            );

            if (
                !$object instanceof PluginWrapper &&
                !$instanceLazy
            ) {
                $plugin->property->setValue($instance, $object);
            }
        }
    }

    private function loadPlugin(
        Plugin $plugin,
        object $instance,
        bool $instanceLazy,
        ContainerProvider $containerProvider
    ): object {
        // Already loaded
        if (
            !$instanceLazy &&
            $plugin->property->isInitialized($instance)
        ) {
            /** @var object $output */
            $output = $plugin->property->getValue($instance);
            return $output;
        }

        // Instant load eager auto
        if ($plugin->strategy->isEagerAuto()) {
            // Use isset to trigger get hooks
            // @phpstan-ignore-next-line
            isset($instance->{$plugin->name});
            return $plugin->load($instance, $containerProvider);
        }

        // Use lazy proxy for instantiable classes
        if ($plugin->isInstantiable()) {
            return $plugin->reflection->newLazyProxy(function ($proxy) use ($plugin, $instance, $containerProvider) {
                return $plugin->load($instance, $containerProvider, $proxy);
            });
        }

        // Use a PluginWrapper as proxy for internal classes and interfaces
        return new PluginWrapper(function () use ($plugin, $instance, $containerProvider) {
            $output = $plugin->load($instance, $containerProvider);
            $name = $plugin->name;
            $this->proxy::$$name = $output;
            return $output;
        });
    }
}

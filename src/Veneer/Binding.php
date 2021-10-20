<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

//declare(strict_types=1);

namespace DecodeLabs\Veneer;

use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;

use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin\AccessTarget as PluginAccessTarget;
use DecodeLabs\Veneer\Plugin\Provider as PluginProvider;

use Psr\Container\ContainerInterface;

use ReflectionClass;

class Binding
{
    /**
     * @var string
     */
    protected $providerClass;

    /**
     * @var string
     */
    protected $proxyClass;


    /**
     * @var Proxy|null
     */
    protected $target;

    /**
     * @var array<string>
     */
    protected $pluginNames = [];


    /**
     * Init with criteria
     */
    public function __construct(string $providerClass, string $proxyClass)
    {
        $this->providerClass = $providerClass;
        $this->proxyClass = $proxyClass;
    }

    /**
     * Extract target object
     *
     * @return $this
     */
    public function bindInstance(?ContainerInterface $container): Binding
    {
        $instance = null;

        // Check container for provider
        if (
            $container &&
            $container->has($this->providerClass)
        ) {
            $instance = $container->get($this->providerClass);
        }

        // Create instance of provider
        if (
            !$instance &&
            (false !== strpos($this->providerClass, '\\')) &&
            class_exists($this->providerClass)
        ) {
            $class = $this->providerClass;
            $instance = new $class();
        }

        if (!$instance) {
            throw Exceptional::Runtime(
                'Could not get instance of ' . $this->providerClass . ' to bind to',
                null,
                $this
            );
        }

        if ($instance instanceof PluginProvider) {
            $this->pluginNames = $instance->getVeneerPluginNames();
        } else {
            $this->pluginNames = [];
        }

        $this->target = $this->createBindingClass($instance);
        $this->target::setVeneerProxyTargetInstance($instance);

        $this->loadPlugins($this->pluginNames);

        return $this;
    }

    /**
     * Has instance been bound to target
     */
    public function hasInstance(): bool
    {
        return $this->target !== null;
    }

    /**
     * Create binding class
     */
    private function createBindingClass(object $instance): Proxy
    {
        $class = $this->generateBindingClass(
            'DecodeLabs\\Veneer\\Binding',
            get_class($instance)
        );

        $class .= 'return new \\DecodeLabs\\Veneer\\Binding\\' . $this->proxyClass . '();' . "\n";

        if (Veneer::shouldCacheBindings()) {
            $hash = md5($class);
            $path = '/tmp/decodelabs/veneer';
            $fileName = $path . '/binding_' . $hash . '.php';

            if (!is_file($fileName)) {
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }

                file_put_contents($fileName, '<?php' . "\n" . $class);
            }

            return require $fileName;
        }

        return eval($class);
    }


    /**
     * Generate binding class definition
     *
     * @param class-string $instanceClass
     */
    public function generateBindingClass(?string $namespace, string $instanceClass): string
    {
        $plugins = $consts = [];
        $ref = new ReflectionClass($instanceClass);
        $instName = $ref->getName();

        $parts = explode('\\', $this->proxyClass);
        $className = array_pop($parts);

        if (!empty($parts)) {
            if (empty($namespace)) {
                $namespace = '';
            } else {
                $namespace .= '\\';
            }

            $namespace .= implode('\\', $parts);
        } elseif ($namespace === '') {
            $namespace = null;
        }

        $class =
            'use DecodeLabs\\Veneer\\Proxy;' . "\n" .
            'use DecodeLabs\\Veneer\\ProxyTrait;' . "\n" .
            'use ' . $instName . ' as Inst;' . "\n" .
            'class ' . $className . ' implements Proxy { use ProxyTrait; ' . "\n";


        $consts['VENEER'] = 'const VENEER = \'' . $this->proxyClass . '\';';
        $consts['VENEER_TARGET'] = 'const VENEER_TARGET = Inst::class;';

        foreach (array_keys($ref->getConstants()) as $key) {
            if ($key === 'VENEER') {
                continue;
            }

            $consts[$key] = 'const ' . $key . ' = Inst::' . $key . ';' . "\n";
        }

        $class .= implode("\n", $consts);

        foreach ($this->pluginNames as $name) {
            $plugins[$name] = 'public static $' . $name . ';';
        }

        $class .= implode("\n", $plugins);
        $class .= '};' . "\n";


        if ($namespace === null) {
            $class = 'namespace {' . "\n" . $class . "\n" . '}';
        } else {
            $class = 'namespace ' . $namespace . ';' . "\n" . $class;
        }

        return $class;
    }





    /**
     * Load plugins from target
     *
     * @param array<string> $pluginNames
     */
    private function loadPlugins(array $pluginNames): void
    {
        foreach ($pluginNames as $name) {
            $loader = function () use ($name) {
                if ($this->target === null) {
                    throw Exceptional::Setup('Target binding has not been created');
                }

                $instance = $this->target::getVeneerProxyTargetInstance();

                if (!$instance instanceof PluginProvider) {
                    return null;
                }

                $output = $instance->loadVeneerPlugin($name);

                if ($instance instanceof PluginAccessTarget) {
                    $instance->cacheLoadedVeneerPlugin($name, $output);
                }

                return $output;
            };

            $this->target::$$name = new class($loader) implements Dumpable {
                public const VENEER_PLUGIN = true;

                /**
                 * @var callable
                 */
                protected $loader;

                /**
                 * @var Plugin|null
                 */
                protected $plugin;


                /**
                 * Init with loader
                 */
                public function __construct(callable $loader)
                {
                    $this->loader = $loader;
                }


                /**
                 * @return mixed
                 */
                public function __get(string $name)
                {
                    if ($this->plugin === null) {
                        $this->loadPlugin();
                    }

                    return $this->plugin->{$name};
                }


                /**
                 * @param array<mixed> $args
                 * @return mixed
                 */
                public function __call(string $name, array $args)
                {
                    if ($this->plugin === null) {
                        $this->loadPlugin();
                    }

                    return $this->plugin->{$name}(...$args);
                }


                private function loadPlugin(): void
                {
                    $loader = $this->loader;
                    $this->plugin = $loader();
                }


                /**
                 * Export for dump inspection
                 *
                 * @return iterable<string, mixed>
                 */
                public function glitchDump(): iterable
                {
                    if ($this->plugin === null) {
                        $this->loadPlugin();
                    }

                    yield 'className' => '@PluginWrapper';
                    yield 'value' => $this->plugin;
                }
            };
        }
    }


    /**
     * Get container provider class
     */
    public function getProviderClass(): string
    {
        return $this->providerClass;
    }

    /**
    * Get facade proxy class
    */
    public function getProxyClass(): string
    {
        return $this->proxyClass;
    }

    /**
     * Get bind target
     */
    public function getTarget(): Proxy
    {
        if ($this->target === null) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->proxyClass . ' has not been bound to target yet',
                null,
                $this
            );
        }

        return $this->target;
    }

    /**
     * Get plugin names
     *
     * @return array<string>
     */
    public function getPluginNames(): array
    {
        if ($this->target === null) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->proxyClass . ' has not been bound to target yet',
                null,
                $this
            );
        }

        return $this->pluginNames;
    }

    /**
     * Has plugin by name
     */
    public function hasPlugin(string $name): bool
    {
        if ($this->target === null) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->proxyClass . ' has not been bound to target yet',
                null,
                $this
            );
        }

        return in_array($name, $this->pluginNames);
    }
}

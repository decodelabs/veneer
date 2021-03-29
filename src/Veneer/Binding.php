<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

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
    protected $name;
    protected $key;

    protected $target;
    protected $pluginNames = [];

    /**
     * Init with criteria
     */
    public function __construct(string $name, string $key)
    {
        $this->name = $name;
        $this->key = $key;
    }

    /**
     * Extract target object
     */
    public function bindInstance(?ContainerInterface $container): Binding
    {
        $instance = null;

        if ($container && $container->has($this->key)) {
            $instance = $container->get($this->key);
        }

        if (!$instance && (false !== strpos($this->key, '\\')) && class_exists($this->key)) {
            $class = $this->key;
            $instance = new $class();
        }

        if (!$instance) {
            throw Exceptional::Runtime(
                'Could not get instance of ' . $this->key . ' to bind to',
                null,
                $this
            );
        }

        if ($instance instanceof PluginProvider) {
            $this->pluginNames = $instance->getVeneerPluginNames();
        } else {
            $this->pluginNames = [];
        }

        $this->target = $this->createBindingClass($instance, $this->pluginNames);
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
    private function createBindingClass(object $instance, array $pluginNames): Proxy
    {
        $plugins = $consts = [];
        $ref = new ReflectionClass($instance);
        $instName = $ref->getName();
        $className = $this->name;

        $class =
            'namespace DecodeLabs\\Veneer\\Binding;' . "\n" .
            'use DecodeLabs\\Veneer\\Proxy;' . "\n" .
            'use DecodeLabs\\Veneer\\ProxyTrait;' . "\n" .
            'use ' . $instName . ' as Inst;' . "\n" .
            'class ' . $className . ' implements Proxy { use ProxyTrait; ' . "\n";


        $consts['VENEER'] = 'const VENEER = \'' . $this->name . '\';';
        $consts['VENEER_TARGET'] = 'const VENEER_TARGET = \'\\' . $instName . '\';';

        foreach (array_keys($ref->getConstants()) as $key) {
            if ($key === 'VENEER') {
                continue;
            }

            $consts[$key] = 'const ' . $key . ' = \\' . $instName . '::' . $key . ';' . "\n";
        }

        if (!empty($consts)) {
            $class .= implode("\n", $consts);
        }

        foreach ($pluginNames as $name) {
            $plugins[$name] = 'public static $' . $name . ';';
        }

        if (!empty($plugins)) {
            $class .= implode("\n", $plugins);
        }

        $class .= '};' . "\n";
        $class .= 'return new ' . $className . '();' . "\n";

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
     * Load plugins from target
     */
    private function loadPlugins(array $pluginNames): void
    {
        foreach ($pluginNames as $name) {
            $loader = function () use ($name) {
                $output = $this->target::$instance->loadVeneerPlugin($name);

                if ($this->target::$instance instanceof PluginAccessTarget) {
                    $this->target::$instance->cacheLoadedVeneerPlugin($name, $output);
                }

                return $output;
            };

            $this->target::$$name = new class($loader) implements Dumpable {
                public const VENEER_PLUGIN = true;

                protected $loader;
                protected $plugin;

                public function __construct(callable $loader)
                {
                    $this->loader = $loader;
                }

                public function __get(string $name)
                {
                    if (!$this->plugin) {
                        $this->loadPlugin();
                    }

                    return $this->plugin->{$name};
                }

                public function __call(string $name, array $args)
                {
                    if (!$this->plugin) {
                        $this->loadPlugin();
                    }

                    return $this->plugin->{$name}(...$args);
                }

                private function loadPlugin()
                {
                    $loader = $this->loader;
                    $this->plugin = $loader();
                }


                /**
                 * Export for dump inspection
                 */
                public function glitchDump(): iterable
                {
                    if (!$this->plugin) {
                        $this->loadPlugin();
                    }

                    yield 'className' => '@PluginWrapper';
                    yield 'value' => $this->plugin;
                }
            };
        }
    }

    /**
     * Get facade name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get container key
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * Get bind target
     */
    public function getTarget(): object
    {
        if (!$this->target) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->name . ' has not been bound to target yet',
                null,
                $this
            );
        }

        return $this->target;
    }

    /**
     * Get plugin names
     */
    public function getPluginNames(): array
    {
        if (!$this->target) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->name . ' has not been bound to target yet',
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
        if (!$this->target) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->name . ' has not been bound to target yet',
                null,
                $this
            );
        }

        return in_array($name, $this->pluginNames);
    }
}

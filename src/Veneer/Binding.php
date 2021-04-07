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
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $key;


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
     *
     * @param array<string> $pluginNames
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
        if ($this->target === null) {
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
     *
     * @return array<string>
     */
    public function getPluginNames(): array
    {
        if ($this->target === null) {
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
        if ($this->target === null) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->name . ' has not been bound to target yet',
                null,
                $this
            );
        }

        return in_array($name, $this->pluginNames);
    }
}

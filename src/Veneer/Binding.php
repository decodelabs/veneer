<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Glitch\Dumpable;
use DecodeLabs\Exceptional;

use Psr\Container\ContainerInterface;

class Binding
{
    protected $name;
    protected $key;
    protected $root = false;
    protected $current = false;
    protected $namespace = null;

    protected $target;
    protected $pluginNames = [];

    /**
     * Init with criteria
     */
    public function __construct(string $name, string $key, bool $root=false, bool $current=false, string $namespace=null)
    {
        $this->name = $name;
        $this->key = $key;
        $this->root = $root;
        $this->current = $current;
        $this->namespace = $namespace;
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
                'Could not get instance of '.$this->key.' to bind to', null, $this
            );
        }

        if ($instance instanceof FacadeTarget) {
            $this->pluginNames = $instance->getFacadePluginNames();
        } else {
            $this->pluginNames = [];
        }

        $this->target = $this->createBindingClass($instance, $this->pluginNames);
        ($this->target)::$instance = $instance;

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
    private function createBindingClass($instance, array $pluginNames): Facade
    {
        $class = 'return new class() implements '.Facade::class.' { use '.FacadeTrait::class.'; ';
        $plugins = $consts = [];
        $ref = new \ReflectionClass($instance);
        $instName = $ref->getName();

        $consts['FACADE'] = 'const FACADE = \''.$this->name.'\';';

        foreach ($ref->getConstants() as $key => $val) {
            if ($key === 'FACADE') {
                continue;
            }

            $consts[$key] = 'const '.$key.' = '.$instName.'::'.$key.';';
        }

        if (!empty($consts)) {
            $class .= implode(' ', $consts).' ';
        }

        foreach ($pluginNames as $name) {
            $plugins[$name] = 'public static $'.$name.';';
        }

        if (!empty($plugins)) {
            $class .= implode(' ', $plugins).' ';
        }

        $class .= '};';
        return eval($class);
    }

    /**
     * Load plugins from target
     */
    private function loadPlugins(array $pluginNames): void
    {
        foreach ($pluginNames as $name) {
            ($this->target)::$$name = new class(function () use ($name) {
                $output = ($this->target)::$instance->loadFacadePlugin($name);

                if (($this->target)::$instance instanceof FacadePluginAccessTarget) {
                    ($this->target)::$instance->cacheLoadedFacadePlugin($name, $output);
                }

                return $output;
            }) implements Dumpable {
                const FACADE_PLUGIN = true;

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
                 * Inspect for Glitch
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
     * Should generate in root?
     */
    public function isRoot(): bool
    {
        return $this->root;
    }

    /**
     * Should generate in current namespace?
     */
    public function isCurrent(): bool
    {
        return $this->current;
    }

    /**
     * Get target namespace
     */
    public function getNamespace(): ?string
    {
        return $this->namespace;
    }

    /**
     * Get bind target
     */
    public function getTarget(): object
    {
        if (!$this->target) {
            throw Exceptional::Runtime(
                'Facade '.$this->name.' has not been bound to target yet', null, $this
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
                'Facade '.$this->name.' has not been bound to target yet', null, $this
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
                'Facade '.$this->name.' has not been bound to target yet', null, $this
            );
        }

        return in_array($name, $this->pluginNames);
    }
}

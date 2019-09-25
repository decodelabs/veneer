<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Glitch\Exception\Factory as Glitch;
use Psr\Container\ContainerInterface;

class Binding
{
    protected $name;
    protected $key;
    protected $root = false;
    protected $current = false;
    protected $namespace = null;

    protected $target;

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
            throw Glitch::ERuntime('Could not get instance of '.$key.' to bind to', null, $this);
        }

        if ($instance instanceof FacadeTarget) {
            $pluginNames = $instance->getFacadePluginNames();
        } else {
            $pluginNames = [];
        }

        $this->target = $this->createBindingClass($instance, $pluginNames);
        ($this->target)::$instance = $instance;

        $this->loadPlugins($pluginNames);

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
            ($this->target)::$$name = new class($name, function (string $name) {
                return ($this->target)::$instance->loadFacadePlugin($name);
            }) {
                const FACADE_PLUGIN = true;

                public static $name;

                protected static $loader;
                protected static $plugin;

                public function __construct(string $name, callable $loader)
                {
                    static::$name = $name;
                    static::$loader = $loader;
                }

                public function __get(string $name)
                {
                    if (!static::$plugin) {
                        $this->loadPlugin();
                    }

                    return static::$plugin->{$name};
                }

                public function __call(string $name, array $args)
                {
                    if (!static::$plugin) {
                        $this->loadPlugin();
                    }

                    return static::$plugin->{$name}(...$args);
                }

                public static function __callStatic(string $name, array $args)
                {
                    if (!static::$plugin) {
                        static::loadPlugin();
                    }

                    return static::$plugin::{$name}(...$args);
                }

                private static function loadPlugin()
                {
                    $loader = static::$loader;
                    static::$plugin = $loader(static::$name);
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
            throw Glitch::ERuntime('Facade '.$this->name.' has not been bound to target yet', null, $this);
        }

        return $this->target;
    }
}

<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer\Loader;

use DecodeLabs\Veneer\Loader;
use DecodeLabs\Veneer\Binding;

use Psr\Container\ContainerInterface;

class Aliasing implements Loader
{
    protected $registered = false;
    protected $facades = [];


    /**
     * Check if loader has been registered
     */
    public function isRegistered(): bool
    {
        return $this->registered;
    }

    /**
     * Register as spl autoloader
     */
    public function register(): Loader
    {
        if (!$this->registered) {
            spl_autoload_register([$this, 'load']);
            $this->registered = true;
        }

        return $this;
    }

    /**
     * Remove from autoloader stack
     */
    public function unregister(): Loader
    {
        if ($this->registered) {
            spl_autoload_unregister([$this, 'load']);
            $this->registered = false;
        }

        return $this;
    }

    /**
     * Attempt to load a class
     */
    public function load(string $class): void
    {
        $parts = explode('\\', $class);
        $name = array_pop($parts);
        $namespace = implode('\\', $parts);

        if (!$facade = ($this->facades[$name] ?? null)) {
            return;
        }

        $class = get_class($facade->getTarget());

        if ($facade->isRoot() && !class_exists($name)) {
            class_alias($class, $name);
        }

        if ($facade->isCurrent() && !empty($namespace) && !class_exists($namespace.'\\'.$name)) {
            class_alias($class, $namespace.'\\'.$name);
        }

        if (null !== ($ns = $facade->getNamespace()) && !class_exists($ns.'\\'.$name)) {
            class_alias($class, $ns.'\\'.$name);
        }
    }


    /**
     * Bind a facade
     */
    public function bind(Binding $facade): Loader
    {
        $this->facades[$facade->getName()] = $facade;
        return $this;
    }

    /**
     * Has facade been bound?
     */
    public function hasFacade(string $name): bool
    {
        return isset($this->facades[$name]);
    }
}

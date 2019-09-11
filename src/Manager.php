<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Veneer\Loader\Aliasing;
use Psr\Container\ContainerInterface;

class Manager
{
    protected static $default;

    protected $container;
    protected $loader;

    /**
     * Set global default manager
     */
    public static function setDefault(?Manager $default): void
    {
        self::$default = $default;
    }

    /**
     * Get global default manager
     */
    public static function getDefault(): ?Manager
    {
        return self::$default;
    }

    /**
     * Init with container and loader
     */
    public function __construct(?ContainerInterface $container=null, ?Loader $loader=null)
    {
        $this->container = $container;
        $this->loader = $loader ?: new Aliasing();
        $this->loader->register();
    }


    /**
     * Add global alias that can be used --anywhere--
     */
    public function bindGlobalFacade(string $name, string $key): Manager
    {
        $this->loader->bind(
            (new Facade($name, $key, true, true))
                ->extractTargetObject($this->container)
        );

        return $this;
    }

    /**
     * Add global alias that can be used --anywhere--
     */
    public function bindLocalFacade(string $name, string $key): Manager
    {
        $this->loader->bind(
            (new Facade($name, $key, false, true))
                ->extractTargetObject($this->container)
        );

        return $this;
    }

    /**
     * Add alias that can be used from root namespace
     */
    public function bindRootFacade(string $name, string $key): Manager
    {
        $this->loader->bind(
            (new Facade($name, $key, true))
                ->extractTargetObject($this->container)
        );

        return $this;
    }

    /**
     * Add alias to specific namespace
     */
    public function bindNamespaceFacade(string $name, string $key, string $namespace): Manager
    {
        $this->loader->bind(
            (new Facade($name, $key, false, false, $namespace))
                ->extractTargetObject($this->container)
        );

        return $this;
    }

    /**
     * Has facade been bound?
     */
    public function hasFacade(string $name): bool
    {
        return $this->loader->hasFacade($name);
    }
}

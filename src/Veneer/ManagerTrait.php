<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

trait ManagerTrait
{
    protected $facades = [];
    protected $container;

    /**
     * Init with container and loader
     */
    public function __construct(?ContainerInterface $container=null)
    {
        $this->container = $container;
    }


    /**
     * Add global alias that can be used --anywhere--
     */
    public function bindGlobalFacade(string $name, string $key): Manager
    {
        $binding = new Binding($name, $key, true, true);
        $this->facades[$binding->getName()] = $binding;
        return $this;
    }

    /**
     * Add local alias that can be used in root namespace
     */
    public function bindLocalFacade(string $name, string $key): Manager
    {
        $binding = new Binding($name, $key, false, true);
        $this->facades[$binding->getName()] = $binding;
        return $this;
    }

    /**
     * Add alias that can be used from root namespace
     */
    public function bindRootFacade(string $name, string $key): Manager
    {
        $binding = new Binding($name, $key, true);
        $this->facades[$binding->getName()] = $binding;
        return $this;
    }

    /**
     * Add alias to specific namespace
     */
    public function bindNamespaceFacade(string $name, string $key, string $namespace): Manager
    {
        $binding = new Facade($name, $key, false, false, $namespace);
        $this->facades[$binding->getName()] = $binding;
        return $this;
    }

    /**
     * Has facade been bound?
     */
    public function hasFacade(string $name): bool
    {
        return isset($this->facades[$name]);
    }


    /**
     * Prepare binding facade and ensure instance has been bound
     */
    public function prepareFacade(string $name): ?Binding
    {
        if (!$facade = ($this->facades[$name] ?? null)) {
            return null;
        }

        if (!$facade->hasInstance()) {
            $facade->bindInstance($this->container);
        }

        return $facade;
    }
}

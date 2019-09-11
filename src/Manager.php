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
    protected $container;
    protected $loader;

    /**
     * Init with container and loader
     */
    public function __construct(ContainerInterface $container, ?Loader $loader=null)
    {
        $this->container = $container;
        $this->loader = $loader ?: new Aliasing();
        $loader->register();
    }


    /**
     * Add global alias that can be used --anywhere--
     */
    public function addGlobalFacade(string $name): Manager
    {
        return $this;
    }

    /**
     * Add alias that can be used from root namespace
     */
    public function addRootFacade(string $name): Manager
    {
        return $this;
    }

    /**
     * Add alias to specific namespace
     */
    public function addNamespaceFacade(string $name): Manager
    {
        return $this;
    }
}

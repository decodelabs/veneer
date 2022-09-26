<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use Attribute;
use DecodeLabs\Exceptional;
use DecodeLabs\Veneer\Plugin\SelfLoader;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Plugin
{
    protected ?string $name = null;

    /**
     * @phpstan-var class-string|null $type
     */
    protected ?string $type = null;

    protected bool $lazy = true;

    protected ?ReflectionProperty $property = null;

    /**
     * @phpstan-var class-string<SelfLoader>|null $loaderClass
     */
    protected ?string $loaderClass = null;


    /**
     * Init with SelfLoader class
     *
     * @phpstan-param class-string<SelfLoader>|null $loaderClass
     */
    public function __construct(?string $loaderClass = null)
    {
        $this->loaderClass = $loaderClass;
    }

    /**
     * Set property
     */
    public function setProperty(ReflectionProperty $property): void
    {
        $this->property = $property;

        $this->setName($name = $property->getName());
        $typeRef = $property->getType();

        if (!$typeRef instanceof ReflectionNamedType) {
            throw Exceptional::Setup('Plugin ' . $name . ' is not a Named type');
        }

        /** @phpstan-var class-string $type */
        $type = $typeRef->getName();
        $this->setType($type);

        $lazyAttr = $property->getAttributes(LazyLoad::class);
        $this->setLazy(!empty($lazyAttr));
    }

    /**
     * Get property
     */
    public function getProperty(): ?ReflectionProperty
    {
        return $this->property;
    }

    /**
     * Set name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Get name
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Set type
     *
     * @phpstan-param class-string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * Get type
     *
     * @phpstan-return class-string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * Set lazy
     */
    public function setLazy(bool $lazy): void
    {
        $this->lazy = $lazy;
    }

    /**
     * Is lazy
     */
    public function isLazy(): bool
    {
        return $this->lazy;
    }


    /**
     * Load instance
     */
    public function load(object $instance): object
    {
        // Check name
        if ($this->name === null) {
            throw Exceptional::Setup('Plugin name has not been defined');
        }

        // Initialised instance
        if (
            $this->property &&
            $this->property->isInitialized($instance) &&
            null !== ($output = $this->property->getValue($instance))
        ) {
            /** @var object $output */
            return $output;
        }


        // Instantiated
        if ($this->type !== null) {
            $class = $this->type;
            $ref = new ReflectionClass($class);

            if ($ref->implementsInterface(SelfLoader::class)) {
                $output = $class::loadAsVeneerPlugin($instance);
            } elseif (
                $this->loaderClass !== null &&
                (new ReflectionClass($this->loaderClass))->implementsInterface(SelfLoader::class)
            ) {
                $class = $this->loaderClass;
                $output = $class::loadAsVeneerPlugin($instance);
            } elseif ($ref->isInstantiable()) {
                $output = new $class($instance);
            } else {
                throw Exceptional::Setup('Loader has no way to instantiate plugin ' . $this->name);
            }


            if ($this->property) {
                $this->property->setValue($instance, $output);
            }

            return $output;
        }

        throw Exceptional::Setup('No loader available for plugin ' . $this->name);
    }
}

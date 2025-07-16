<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use Attribute;
use DecodeLabs\Exceptional;
use DecodeLabs\Slingshot;
use DecodeLabs\Veneer\Plugin\Strategy;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Plugin
{
    public string $name {
        get => $this->property->getName();
    }

    /**
     * @var class-string $type
     */
    public string $type {
        get {
            if (!isset($this->type)) {
                $typeRef = $this->property->getType();

                if (!$typeRef instanceof ReflectionNamedType) {
                    throw Exceptional::Setup(
                        message: 'Plugin ' . $this->name . ' is not a Named type'
                    );
                }

                /** @var class-string $type */
                $type = $typeRef->getName();
                $this->type = $type;
            }

            return $this->type;
        }
    }

    /**
     * @var class-string $instanceType
     */
    public string $instanceType {
        get => $this->instanceType ?? $this->type;
    }

    public ReflectionProperty $property {
        get {
            if (!isset($this->property)) {
                throw Exceptional::Runtime(
                    message: 'Plugin property has not been defined'
                );
            }

            return $this->property;
        }
        set {
            if (
                isset($this->property) &&
                $this->property !== $value
            ) {
                throw Exceptional::Runtime(
                    message: 'Plugin property is already defined'
                );
            }

            $this->property = $value;
        }
    }

    public protected(set) Strategy $strategy = Strategy::Manual;

    public bool $auto {
        get => $this->strategy->isAuto();
    }

    public bool $lazy {
        get => $this->strategy->isLazy();
    }


    /**
     * @var ReflectionClass<object> $reflection
     */
    public ReflectionClass $reflection {
        get {
            if (!isset($this->reflection)) {
                $this->reflection = new ReflectionClass($this->type);
            }

            return $this->reflection;
        }
    }


    /**
     * Init with strategy options
     *
     * @param ?class-string<object> $type
     */
    public function __construct(
        bool $auto = false,
        bool $lazy = false,
        ?string $type = null
    ) {
        if ($lazy) {
            $this->strategy = Strategy::Lazy;
        } elseif ($auto) {
            $this->strategy = Strategy::Auto;
        } else {
            $this->strategy = Strategy::Manual;
        }

        if ($type !== null) {
            $this->instanceType = $type;
        }
    }

    public function isInstantiable(): bool
    {
        return
            $this->reflection->isInstantiable() &&
            !$this->reflection->isInternal();
    }

    public function requiresWrapper(): bool
    {
        return
            !$this->isInstantiable() &&
            (
                $this->strategy->isManual() ||
                $this->strategy->isLazy()
            );
    }


    /**
     * Load instance
     */
    public function load(
        object $instance,
        ContainerProvider $containerProvider,
        ?object $proxy = null
    ): object {
        // Initialised instance
        if (
            isset($instance->{$this->name}) &&
            $this->property->isInitialized($instance) &&
            !$this->property->isLazy($instance) &&
            null !== ($output = $this->property->getValue($instance))
        ) {
            if ($output !== $proxy) {
                /** @var object $output */
                return $output;
            }
        }


        // Instantiated
        if (!$this->auto) {
            throw Exceptional::Setup(
                message: 'Manual plugin property ' . get_class($instance) . '::$' . $this->name . ' has not been instantiated by constructor',
                data: $instance
            );
        }

        $output = $this->instantiate($instance, $containerProvider);
        $this->property->setValue($instance, $output);

        return $output;
    }

    private function instantiate(
        object $instance,
        ContainerProvider $containerProvider
    ): object {
        $reflection = new ReflectionClass($this->instanceType);

        if (!$reflection->isInstantiable()) {
            throw Exceptional::Setup(
                message: 'Loader has no way to instantiate plugin ' . $this->name
            );
        }

        if (class_exists(Slingshot::class)) {
            $slingshot = new Slingshot($containerProvider->container);
            $slingshot->addType($instance);
            $output = $slingshot->newInstance($this->instanceType);

            /** @var object $output */
            return $output;
        }

        if (!$this->reflection->hasMethod('__construct')) {
            return $this->reflection->newInstance();
        }

        $constructor = $this->reflection->getConstructor();

        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            if (!$parameter->isOptional()) {
                throw Exceptional::ComponentUnavailable(
                    message: 'Cannot resolve constructor dependencies without Slingshot'
                );
            }
        }

        return $this->reflection->newInstance();
    }
}

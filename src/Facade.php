<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use Psr\Container\ContainerInterface;

class Facade
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

        $this->target = new class() {
            public static $object;

            public static function __callStatic(string $name, array $args)
            {
                if (!self::$object) {
                    \Glitch::ERuntime('No target object has been bound in '.$name.' facade');
                }

                return (self::$object)->{$name}(...$args);
            }
        };
    }

    /**
     * Extract target object
     */
    public function extractTargetObject(?ContainerInterface $container): Facade
    {
        if ($container && $container->has($this->key)) {
            ($this->target)::$object = $container->get($this->key);
        }

        if (!($this->target)::$object && (false !== strpos($this->key, '\\')) && class_exists($this->key)) {
            ($this->target)::$object = new $this->key();
        }

        return $this;
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
        return $this->target;
    }
}

<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

//declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

use ArrayAccess;
use Closure;
use DecodeLabs\Exceptional;
use DecodeLabs\Glitch\Dumpable;
use IteratorAggregate;
use Traversable;

/**
 * @template T of object
 * @mixin T
 * @implements ArrayAccess<mixed, mixed>
 * @implements IteratorAggregate<mixed, mixed>
 */
class Wrapper implements
    ArrayAccess,
    IteratorAggregate,
    Dumpable
{
    /**
     * @var Closure(): T
     */
    protected Closure $loader;

    /**
     * @var T|null
     */
    protected ?object $plugin = null;


    /**
     * Init with loader
     *
     * @param callable(): T $loader
     */
    public function __construct(
        callable $loader
    ) {
        $this->loader = Closure::fromCallable($loader);
    }


    public function __get(
        string $name
    ): mixed {
        if ($this->plugin === null) {
            $this->getVeneerPlugin();
        }

        return $this->plugin->{$name};
    }


    /**
     * @param array<mixed> $args
     */
    public function __call(
        string $name,
        array $args
    ): mixed {
        if ($this->plugin === null) {
            $this->getVeneerPlugin();
        }

        return $this->plugin->{$name}(...$args);
    }

    /**
     * @return T
     */
    public function getVeneerPlugin(): object
    {
        $loader = $this->loader;
        $this->plugin = $loader();

        return $this->plugin;
    }


    /**
     * Set offset
     */
    public function offsetSet(
        mixed $offset,
        mixed $value
    ): void {
        if ($this->plugin === null) {
            $this->getVeneerPlugin();
        }

        if (!$this->plugin instanceof ArrayAccess) {
            throw Exceptional::Runtime(
                message: 'Plugin does not implement ArrayAccess'
            );
        }

        $this->plugin->offsetSet($offset, $value);
    }

    /**
     * Get offset
     */
    public function offsetGet(
        mixed $offset
    ): mixed {
        if ($this->plugin === null) {
            $this->getVeneerPlugin();
        }

        if (!$this->plugin instanceof ArrayAccess) {
            throw Exceptional::Runtime(
                message: 'Plugin does not implement ArrayAccess'
            );
        }

        return $this->plugin->offsetGet($offset);
    }

    /**
     * Check if offset exists
     */
    public function offsetExists(
        mixed $offset
    ): bool {
        if ($this->plugin === null) {
            $this->getVeneerPlugin();
        }

        if (!$this->plugin instanceof ArrayAccess) {
            throw Exceptional::Runtime(
                message: 'Plugin does not implement ArrayAccess'
            );
        }

        return $this->plugin->offsetExists($offset);
    }

    /**
     * Unset offset
     */
    public function offsetUnset(
        mixed $offset
    ): void {
        if ($this->plugin === null) {
            $this->getVeneerPlugin();
        }

        if (!$this->plugin instanceof ArrayAccess) {
            throw Exceptional::Runtime(
                message: 'Plugin does not implement ArrayAccess'
            );
        }

        $this->plugin->offsetUnset($offset);
    }


    /**
     * Get iterator
     */
    public function getIterator(): Traversable
    {
        if ($this->plugin === null) {
            $this->getVeneerPlugin();
        }

        if ($this->plugin instanceof IteratorAggregate) {
            return $this->plugin->getIterator();
        }

        if (!$this->plugin instanceof Traversable) {
            throw Exceptional::Runtime(
                message: 'Plugin does not implement Traversable'
            );
        }

        return $this->plugin;
    }


    /**
     * Export for dump inspection
     *
     * @return iterable<string, mixed>
     */
    public function glitchDump(): iterable
    {
        if ($this->plugin === null) {
            $this->getVeneerPlugin();
        }

        yield 'className' => '@PluginWrapper';
        yield 'value' => $this->plugin;
    }
}

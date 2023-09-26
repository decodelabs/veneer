<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

//declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

use Closure;
use DecodeLabs\Glitch\Dumpable;

/**
 * @template T of object
 * @mixin T
 */
class Wrapper implements Dumpable
{
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
    public function __construct(callable $loader)
    {
        $this->loader = Closure::fromCallable($loader);
    }


    public function __get(string $name): mixed
    {
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

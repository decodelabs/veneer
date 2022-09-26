<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

use DecodeLabs\Glitch\Dumpable;

class Wrapper implements Dumpable
{
    public const VENEER_PLUGIN = true;

    /**
     * @var callable
     */
    protected $loader;

    /**
     * @var object|null
     */
    protected $plugin;


    /**
     * Init with loader
     */
    public function __construct(callable $loader)
    {
        $this->loader = $loader;
    }


    /**
     * @return mixed
     */
    public function __get(string $name)
    {
        if ($this->plugin === null) {
            $this->loadPlugin();
        }

        return $this->plugin->{$name};
    }


    /**
     * @param array<mixed> $args
     * @return mixed
     */
    public function __call(string $name, array $args)
    {
        if ($this->plugin === null) {
            $this->loadPlugin();
        }

        return $this->plugin->{$name}(...$args);
    }


    private function loadPlugin(): void
    {
        $loader = $this->loader;
        $this->plugin = $loader();
    }


    /**
     * Export for dump inspection
     *
     * @return iterable<string, mixed>
     */
    public function glitchDump(): iterable
    {
        if ($this->plugin === null) {
            $this->loadPlugin();
        }

        yield 'className' => '@PluginWrapper';
        yield 'value' => $this->plugin;
    }
}

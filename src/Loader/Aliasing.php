<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer\Loader;

use DecodeLabs\Veneer\Loader;

class Aliasing implements Loader
{
    protected $registered = false;

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

        dd($name, $namespace);
    }
}

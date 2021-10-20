<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Stub;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Exceptional;
use DecodeLabs\Veneer\Binding;

class Generator
{
    /**
     * @var Dir
     */
    protected $dir;

    /**
     * Init with stub directory location
     */
    public function __construct(Dir $dir)
    {
        $this->dir = $dir;
    }

    /**
     * Generate stub
     */
    public function generate(Binding $binding): void
    {
        $code = <<<PHP
<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */

PHP;
        $proxyClass = $binding->getProxyClass();
        $parts = explode('\\', $proxyClass);
        $fileName = implode('/', $parts);
        array_pop($parts);

        if (empty($parts)) {
            $namespace = null;
        } else {
            $namespace = implode('\\', $parts);
        }

        $instance = $binding->getTarget()->getVeneerProxyTargetInstance();

        if (!is_object($instance)) {
            throw Exceptional::Setup($proxyClass . ' instance has not been bound');
        }

        $code .= $binding->generateBindingClass(
            $namespace,
            get_class($instance)
        );

        $this->dir->createFile($fileName . '.php', $code);
    }
}

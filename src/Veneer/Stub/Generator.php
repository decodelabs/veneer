<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Stub;

use DecodeLabs\Atlas\Dir;
use DecodeLabs\Exceptional;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Binding;

use ReflectionClass;

class Generator
{
    protected Dir $scanDir;
    protected Dir $stubDir;

    /**
     * Init with stub directory location
     */
    public function __construct(
        Dir $scanDir,
        Dir $stubDir
    ) {
        $this->scanDir = $scanDir;
        $this->stubDir = $stubDir;
    }

    /**
     * Scan for bindings
     *
     * @return array<Binding>
     */
    public function scan(): array
    {
        $bindings = [];
        $manager = Veneer::getDefaultManager();

        foreach ($manager->getBindings() as $binding) {
            $ref = new ReflectionClass($binding->getProviderClass());

            if (!$file = $ref->getFileName()) {
                continue;
            }

            if (
                0 !== strpos($file, (string)$this->scanDir) ||
                0 === strpos($file, (string)$this->scanDir . '/vendor')
            ) {
                continue;
            }

            $bindings[] = $binding;
        }

        return $bindings;
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

        $instance = $binding->getTarget()->getVeneerProxyTargetInstance();

        if (!is_object($instance)) {
            throw Exceptional::Setup($proxyClass . ' instance has not been bound');
        }

        $code .= $binding->generateBindingClass(
            null,
            get_class($instance),
            true
        );

        $this->stubDir->ensureExists();
        $this->stubDir->createFile($fileName . '.php', $code);
    }
}

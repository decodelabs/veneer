<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Stub;

use DecodeLabs\Exceptional;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Binding;
use DirectoryIterator;
use ReflectionClass;
use Throwable;

class Generator
{
    protected string $scanDir;
    protected string $stubDir;

    /**
     * Init with stub directory location
     */
    public function __construct(
        string $scanDir,
        string $stubDir
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
        foreach (new DirectoryIterator($this->scanDir . '/src') as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $path = $file->getPathname();

            if (
                !str_ends_with($path, '.php') ||
                str_ends_with($path, 'ootstrap.php')
            ) {
                continue;
            }

            try {
                require_once $file->getPathname();
            } catch (Throwable $e) {
                continue;
            }
        }


        $bindings = [];
        $manager = Veneer::getDefaultManager();

        foreach ($manager->getBindings() as $binding) {
            $ref = new ReflectionClass($binding->getProviderClass());

            if (!$file = $ref->getFileName()) {
                continue;
            }

            if (
                0 !== strpos($file, $this->scanDir) ||
                0 === strpos($file, $this->scanDir . '/vendor')
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
    public function generate(
        Binding $binding
    ): void {
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

        $filePath = $this->stubDir . '/' . $fileName . '.php';
        $dirPath = dirname($filePath);

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        file_put_contents($filePath, $code);
    }
}

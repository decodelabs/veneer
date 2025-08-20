<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Stub;

use DecodeLabs\Veneer\Binding;
use DecodeLabs\Veneer\Manager;
use DecodeLabs\Veneer\Proxy\ClassGenerator;
use DirectoryIterator;
use ReflectionClass;
use Throwable;

class Generator
{
    protected string $scanDir;
    protected string $stubDir;

    public function __construct(
        string $scanDir,
        string $stubDir
    ) {
        $this->scanDir = $scanDir;
        $this->stubDir = $stubDir;
    }

    /**
     * @return array<Binding>
     */
    public function scan(): array
    {
        $this->loadRootFiles();
        $bindings = [];

        foreach (Manager::getGlobalManager()->getBindings(mount: false) as $binding) {
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

    protected function loadRootFiles(): void
    {
        if (!is_dir($this->scanDir . '/src')) {
            return;
        }

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

            if (false === ($contents = file_get_contents($path))) {
                continue;
            }

            if (
                !str_contains($contents, 'Veneer::register') &&
                !str_contains($contents, '::getGlobalManager()->register')
            ) {
                continue;
            }

            try {
                require_once $file->getPathname();
            } catch (Throwable $e) {
                continue;
            }
        }
    }

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

        $generator = new ClassGenerator($binding);

        $code .= $generator->generate(
            withMethods: true
        );

        $filePath = $this->stubDir . '/' . $fileName . '.php';
        $dirPath = dirname($filePath);

        if (!is_dir($dirPath)) {
            mkdir($dirPath, 0777, true);
        }

        file_put_contents($filePath, $code);
    }
}

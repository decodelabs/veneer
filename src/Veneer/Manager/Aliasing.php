<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer\Manager;

use DecodeLabs\Veneer\Manager;
use DecodeLabs\Veneer\ManagerTrait;
use DecodeLabs\Veneer\Binding;

use Psr\Container\ContainerInterface;

class Aliasing implements Manager
{
    use ManagerTrait;

    /**
     * Load facade from class name
     */
    public function load(string $name, ?string $namespace): bool
    {
        if (!$facade = $this->prepareFacade($name)) {
            return false;
        }

        $class = $name;

        if ($namespace !== null) {
            $class = $namespace.'\\'.$class;
        }

        $facadeClass = get_class($facade->getTarget());
        $output = false;

        if ($facade->isRoot() && $class === $name && !class_exists($name)) {
            class_alias($facadeClass, $name);
            $output = true;
        }

        if ($facade->isCurrent() && !empty($namespace) && !class_exists($namespace.'\\'.$name)) {
            class_alias($facadeClass, $namespace.'\\'.$name);
            $output = true;
        }

        if (null !== ($ns = $facade->getNamespace()) && $class === $ns.'\\'.$name && !class_exists($ns.'\\'.$name)) {
            class_alias($facadeClass, $ns.'\\'.$name);
            $output = true;
        }

        return $output;
    }

    /**
     * Manually load an alias against the name
     */
    public function loadManual(string $name, string $className): bool
    {
        if (!$facade = $this->prepareFacade($name)) {
            return false;
        }

        if (class_exists($className, false)) {
            return false;
        }

        $facadeClass = get_class($facade->getTarget());
        class_alias($facadeClass, $className);
        return true;
    }
}

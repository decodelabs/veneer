<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer\Manager;

use DecodeLabs\Veneer\Manager;
use DecodeLabs\Veneer\ManagerTrait;

use Psr\Container\ContainerInterface;

class Aliasing implements Manager
{
    use ManagerTrait;

    /**
     * Manually load an alias against the name
     */
    public function load(string $name, string $className): bool
    {
        if (!$binding = $this->prepare($name)) {
            return false;
        }

        if (class_exists($className, false)) {
            return false;
        }

        $bindingClass = get_class($binding->getTarget());
        class_alias($bindingClass, $className);
        return true;
    }
}

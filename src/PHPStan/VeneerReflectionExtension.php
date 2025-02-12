<?php

/**
 * @package PHPStanDecodeLabs
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\PHPStan;

use DecodeLabs\Coercion;
use DecodeLabs\PHPStan\StaticMethodReflection;
use DecodeLabs\Veneer\Proxy;
use Exception;
use PHPStan\Analyser\OutOfClassScope;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\MethodReflection as MethodReflectionInterface;
use PHPStan\Reflection\MethodsClassReflectionExtension;
use PHPStan\Reflection\ReflectionProvider;

class VeneerReflectionExtension implements MethodsClassReflectionExtension
{
    protected ReflectionProvider $reflectionProvider;

    public function __construct(
        ReflectionProvider $reflectionProvider
    ) {
        $this->reflectionProvider = $reflectionProvider;
    }

    public function hasMethod(
        ClassReflection $classReflection,
        string $methodName
    ): bool {
        $class = $classReflection->getName();

        if ($classReflection->implementsInterface(Proxy::class)) {
            return $this->reflectionProvider->getClass(
                Coercion::toString($class::VeneerTarget)
            )
                ->hasMethod($methodName);
        }

        return false;
    }

    public function getMethod(
        ClassReflection $classReflection,
        string $methodName
    ): MethodReflectionInterface {
        $class = $classReflection->getName();

        if (!$classReflection->implementsInterface(Proxy::class)) {
            throw new Exception('Unable to get method');
        }

        return new StaticMethodReflection(
            $this->reflectionProvider->getClass(
                Coercion::toString($class::VeneerTarget)
            )
                ->getMethod($methodName, new OutOfClassScope())
        );
    }
}

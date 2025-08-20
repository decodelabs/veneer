<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Proxy;

use DecodeLabs\Exceptional;
use DecodeLabs\Veneer\Binding;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

class ClassGenerator
{
    public function __construct(
        public Binding $binding,

        /** @var class-string<object>|null */
        public ?string $instanceClass = null,
    ) {
    }

    public function generate(
        ?string $namespace = null,
        bool $withMethods = false,
    ): string {
        if ($this->instanceClass === null) {
            if ($this->binding->hasInstance()) {
                $instance = $this->binding->getInstance();

                if (!is_object($instance)) {
                    throw Exceptional::Setup($this->binding->getProxyClass() . ' instance has not been bound');
                }

                $this->instanceClass = get_class($instance);
            } else {
                $this->instanceClass = $this->binding->getProviderClass();
            }
        }

        $ref = new ReflectionClass($this->instanceClass);
        $instName = $ref->getName();

        // Normalize namespace
        $parts = explode('\\', $this->binding->getProxyClass());
        $className = array_pop($parts);

        if (!empty($parts)) {
            if (empty($namespace)) {
                $namespace = '';
            } else {
                $namespace .= '\\';
            }

            $namespace .= implode('\\', $parts);
        } elseif ($namespace === '') {
            $namespace = null;
        }


        // Initialization
        $plugins = $this->binding->getPlugins();
        $properties = $consts = $uses = [];
        $class = $methodDef = '';


        // Uses
        $uses['Proxy'] = 'DecodeLabs\\Veneer\\Proxy';
        $uses['ProxyTrait'] = 'DecodeLabs\\Veneer\\ProxyTrait';
        $uses['Inst'] = $instName;
        $wrapper = false;

        if ($withMethods) {
            $properties['instance'] = 'protected static Inst $_veneerInstance;';
        }

        foreach ($plugins as $name => $plugin) {
            $uses[ucfirst($name) . 'Plugin'] = $plugin->type;
            $pluginType = $type = ucfirst($name) . 'Plugin';

            if ($plugin->requiresWrapper()) {
                $wrapper = true;
                $type .= '|PluginWrapper';
                $properties[$name . '-comment'] = '/** @var ' . $type . '<' . $pluginType . '> $' . $name . ' */';
            }

            $properties[$name] = 'public static ' . $type . ' $' . $name . ';';
        }

        if ($wrapper) {
            $uses['PluginWrapper'] = 'DecodeLabs\\Veneer\\Plugin\\Wrapper';
        }

        if ($withMethods) {
            $methodDef = $this->listClassMethods($ref, $uses);
        }

        foreach ($uses as $alias => $target) {
            $class .= 'use ' . $target;
            $class .= ' as ' . $alias;
            $class .= ';' . "\n";
        }


        // Class structure
        $class .= "\n" .
            'class ' . $className . ' implements Proxy' . "\n" .
            '{' . "\n" .
            '    use ProxyTrait;' . "\n\n";


        // Constants
        $consts['Veneer'] = 'public const Veneer = \'' . addslashes($this->binding->getProxyClass()) . '\';';
        $consts['VeneerTarget'] = 'public const VeneerTarget = Inst::class;';

        foreach ($ref->getReflectionConstants() as $const) {
            $key = $const->getName();

            if ($key === 'Veneer') {
                continue;
            }

            if ($const->isPublic()) {
                $consts[$key] = 'public const ' . $key . ' = Inst::' . $key . ';';
            }
        }

        $class .= '    ' . implode("\n    ", $consts) . "\n";


        // Properties
        if (!empty($properties)) {
            $class .= "\n";
            $class .= '    ' . implode("\n    ", $properties) . "\n";
        }


        // Methods
        if ($withMethods) {
            $class .= "\n" . $methodDef;
        }

        // End
        $class .= '};' . "\n";


        // Namespace
        if ($namespace === null) {
            $class = 'namespace {' . "\n" . $class . "\n" . '}';
        } else {
            $class = 'namespace ' . $namespace . ';' . "\n\n" . $class;
        }

        return $class;
    }

    /**
     * @template T of object
     * @param ReflectionClass<T> $ref
     * @param array<string, string> $uses
     */
    private function listClassMethods(
        ReflectionClass $ref,
        array &$uses
    ): string {
        $methods = [];

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $name = $method->getName();

            if (substr($name, 0, 2) === '__') {
                continue;
            }

            $string = 'public static function ';
            $string .= $name . '(';
            $params = [];

            foreach ($method->getParameters() as $parameter) {
                $param = '';

                if (null !== ($type = $parameter->getType())) {
                    $param .= $this->exportType($type, $uses) . ' ';
                }

                if ($parameter->isVariadic()) {
                    $param .= '...';
                }

                $param .= '$' . $parameter->getName();

                if ($parameter->isDefaultValueAvailable()) {
                    $default = $parameter->getDefaultValue();

                    if ($default === []) {
                        $exp = '[]';
                    } else {
                        $exp = var_export($default, true);
                    }

                    $param .= ' = ' . $exp;
                }

                $params[] = $param;
            }

            $string .= implode(', ', $params) . ')';

            if (null !== ($type = $method->getReturnType())) {
                $type = $this->exportType($type, $uses);
                $string .= ': ' . $type . ' ';
            }

            $string .= '{';

            if (
                $type !== 'void' &&
                $type !== 'never'
            ) {
                $string .= "\n";
                $string .= '        return static::$_veneerInstance->' . $name . '(';

                if (!empty($params)) {
                    $string .= '...func_get_args()';
                }

                $string .= ');' . "\n";
                $string .= '    ';
            }

            $string .= '}';

            $methods[$name] = $string;
        }

        if (empty($methods)) {
            return '';
        }

        return '    ' . implode("\n    ", $methods) . "\n";
    }

    /**
     * @param array<string, string> $uses
     */
    private function exportType(
        ReflectionType $type,
        array &$uses
    ): string {
        if ($type instanceof ReflectionNamedType) {
            return $this->exportNamedType($type, $uses);
        } elseif ($type instanceof ReflectionUnionType) {
            return $this->exportUnionType($type, $uses);
        } elseif ($type instanceof ReflectionIntersectionType) {
            return $this->exportIntersectionType($type, $uses);
        }

        throw Exceptional::Runtime(
            message: 'Unknown type reflection',
            data: $type
        );
    }

    /**
     * @param array<string, string> $uses
     */
    private function exportNamedType(
        ReflectionNamedType $type,
        array &$uses
    ): string {
        $name = $type->getName();

        if ($type->isBuiltin()) {
            $output = $name;
        } elseif (
            $name === 'static' ||
            $name === 'self' ||
            $name === 'parent'
        ) {
            return 'Inst';
        } else {
            /** @var int */
            static $ref = 0;
            $test = array_flip($uses);

            if (!array_key_exists($name, $test)) {
                $key = 'Ref' . $ref++;
                $uses[$key] = $name;
                $test[$name] = $key;
            }

            $output = $test[$name];

            /** @phpstan-ignore-next-line */
            if ($output === null) {
                $parts = explode('\\', $name);
                $output = array_pop($parts);
            }
        }

        if (
            $type->allowsNull() &&
            $output !== 'mixed' &&
            $output !== 'null' &&
            $output !== 'true' &&
            $output !== 'false'
        ) {
            $output = '?' . $output;
        }

        return $output;
    }

    /**
     * @param array<string, string> $uses
     */
    private function exportUnionType(
        ReflectionUnionType $type,
        array &$uses
    ): string {
        $output = [];

        foreach ($type->getTypes() as $inner) {
            $output[] = $this->exportType($inner, $uses);
        }

        return implode('|', $output);
    }

    /**
     * @param array<string, string> $uses
     */
    private function exportIntersectionType(
        ReflectionIntersectionType $type,
        array &$uses
    ): string {
        $output = [];

        foreach ($type->getTypes() as $inner) {
            $output[] = $this->exportType($inner, $uses);
        }

        return implode('&', $output);
    }
}

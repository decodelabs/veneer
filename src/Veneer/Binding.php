<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

//declare(strict_types=1);

namespace DecodeLabs\Veneer;

use DecodeLabs\Exceptional;
use DecodeLabs\Pandora\Container as PandoraContainer;
use DecodeLabs\Slingshot;
use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;

use Psr\Container\ContainerInterface;

use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionObject;
use ReflectionType;
use ReflectionUnionType;

class Binding
{
    /**
     * @var class-string
     */
    protected string $providerClass;

    /**
     * @var class-string
     */
    protected string $proxyClass;

    protected ?Proxy $target = null;
    protected bool $deferred = false;

    /**
     * @var array<string, Plugin>|null
     */
    protected ?array $plugins = null;


    /**
     * Init with criteria
     *
     * @param class-string $providerClass
     * @param class-string $proxyClass
     */
    public function __construct(
        string $providerClass,
        string $proxyClass
    ) {
        $this->providerClass = $providerClass;
        $this->proxyClass = $proxyClass;
    }


    /**
     * Is provider lazy loader
     */
    public function isLazyLoader(): bool
    {
        $ref = new ReflectionClass($this->providerClass);
        $eager = $ref->getAttributes(EagerLoad::class);
        return empty($eager);
    }

    /**
     * Is deferred
     */
    public function isDeferred(): bool
    {
        return $this->deferred;
    }

    /**
     * Resolve deferral
     */
    public function resolveDeferral(
        ?ContainerInterface $container = null
    ): void {
        if (
            !$this->deferred ||
            !$this->target ||
            null === ($instance = $this->target::getVeneerProxyTargetInstance())
        ) {
            return;
        }

        $this->deferred = false;

        // Call constructor
        $ref = new ReflectionObject($instance);
        $method = $ref->getMethod('__construct');
        $method->setAccessible(true);

        // Return value doc mismatch
        // @phpstan-ignore-next-line
        if (!$closure = $method->getClosure($instance)) {
            throw Exceptional::Logic(
                'Unable to get closure for constructor of ' . $this->providerClass
            );
        }

        if (class_exists(Slingshot::class)) {
            // Invoke constructor with Slingshot
            (new Slingshot($container))->invoke($closure);
        } else {
            foreach ($method->getParameters() as $parameter) {
                if (!$parameter->isOptional()) {
                    throw Exceptional::ComponentUnavailable(
                        'Cannot resolve constructor dependencies without Slingshot'
                    );
                }
            }

            // Invoke constructor directly
            $closure();
        }

        // Load plugins
        $this->loadPlugins();
    }


    /**
     * Extract target object
     *
     * @return $this
     */
    public function bindInstance(
        ?ContainerInterface $container
    ): Binding {
        $instance = null;
        $this->deferred = false;

        // Check container for provider
        if (
            $container &&
            $container->has($this->providerClass)
        ) {
            $instance = $container->get($this->providerClass);
        }

        // Create instance of provider
        if (
            !$instance &&
            (false !== strpos($this->providerClass, '\\')) &&
            class_exists($this->providerClass)
        ) {
            $ref = new ReflectionClass($this->providerClass);
            $instance = $ref->newInstanceWithoutConstructor();
            $this->deferred = $ref->hasMethod('__construct');
        }

        // Check instance
        if (!is_object($instance)) {
            throw Exceptional::Runtime(
                'Could not get instance of ' . $this->providerClass . ' to bind to',
                null,
                $this
            );
        }

        // Load plugin names
        $this->scanPlugins($instance);

        // Create target
        $this->target = $this->createBindingClass(get_class($instance));
        $this->target::setVeneerProxyTargetInstance($instance);

        if (!$this->deferred) {
            $this->loadPlugins();

            if (
                $container instanceof PandoraContainer &&
                !$container->has($this->providerClass)
            ) {
                $container->bindShared($this->providerClass, $instance);
            }
        }

        return $this;
    }



    /**
     * Has instance been bound to target
     */
    public function hasInstance(): bool
    {
        return $this->target !== null;
    }

    /**
     * Create binding class
     *
     * @param class-string $instanceClass
     */
    private function createBindingClass(
        string $instanceClass
    ): Proxy {
        $class = $this->generateBindingClass(
            'DecodeLabs\\Veneer\\Binding',
            $instanceClass
        );

        $class .= 'return new \\DecodeLabs\\Veneer\\Binding\\' . $this->proxyClass . '();' . "\n";

        if (Veneer::shouldCacheBindings()) {
            $hash = md5($class);
            $path = '/tmp/decodelabs/veneer';
            $fileName = $path . '/binding_' . $hash . '.php';

            if (!is_file($fileName)) {
                if (!is_dir($path)) {
                    mkdir($path, 0755, true);
                }

                file_put_contents($fileName, '<?php' . "\n" . $class);
            }

            return require $fileName;
        }

        return eval($class);
    }


    /**
     * Generate binding class definition
     *
     * @param class-string $instanceClass
     */
    public function generateBindingClass(
        ?string $namespace,
        string $instanceClass,
        bool $listMethods = false
    ): string {
        $ref = new ReflectionClass($instanceClass);
        $instName = $ref->getName();

        // Normalize namespace
        $parts = explode('\\', $this->proxyClass);
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
        $plugins = $this->getPlugins();
        $properties = $consts = $uses = [];
        $class = $methodDef = '';


        // Uses
        $uses['Proxy'] = 'DecodeLabs\\Veneer\\Proxy';
        $uses['ProxyTrait'] = 'DecodeLabs\\Veneer\\ProxyTrait';
        $uses['Inst'] = $instName;
        $wrapper = false;

        if ($listMethods) {
            $properties['instance'] = 'public static Inst $instance;';
        }

        foreach ($plugins as $name => $plugin) {
            $uses[ucfirst($name) . 'Plugin'] = $plugin->getType();
            $pluginType = $type = ucfirst($name) . 'Plugin';

            if ($plugin->isLazy()) {
                $wrapper = true;
                $type .= '|PluginWrapper';
                $properties[$name . '-comment'] = '/** @var ' . $type . '<' . $pluginType . '> $' . $name . ' */';
            }

            $properties[$name] = 'public static ' . $type . ' $' . $name . ';';
        }

        if ($wrapper) {
            $uses['PluginWrapper'] = 'DecodeLabs\\Veneer\\Plugin\\Wrapper';
        }

        if ($listMethods) {
            $methodDef = $this->listClassMethods($ref, $uses);
        }

        foreach ($uses as $alias => $target) {
            $class .= 'use ' . $target;

            if ($alias !== null) {
                $class .= ' as ' . $alias;
            }

            $class .= ';' . "\n";
        }


        // Class structure
        $class .= "\n" .
            'class ' . $className . ' implements Proxy' . "\n" .
            '{' . "\n" .
            '    use ProxyTrait;' . "\n\n";


        // Constants
        $consts['VENEER'] = 'const VENEER = \'' . addslashes($this->proxyClass) . '\';';
        $consts['VENEER_TARGET'] = 'const VENEER_TARGET = Inst::class;';

        foreach ($ref->getReflectionConstants() as $const) {
            $key = $const->getName();

            if ($key === 'VENEER') {
                continue;
            }

            if ($const->isPublic()) {
                $consts[$key] = 'const ' . $key . ' = Inst::' . $key . ';';
            }
        }

        $class .= '    ' . implode("\n    ", $consts) . "\n";


        // Properties
        if (!empty($properties)) {
            $class .= "\n";
            $class .= '    ' . implode("\n    ", $properties) . "\n";
        }


        // Methods
        if ($listMethods) {
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
     * List instance class methods
     *
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

            if ($type !== 'void') {
                $string .= "\n";
                $string .= '        return static::$instance->' . $name . '(';

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
     * Export type reflection
     *
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

        throw Exceptional::Runtime('Unknown type reflection', null, $type);
    }

    /**
     * Export named type reflection
     *
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
     * Export union type reflection
     *
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
     * Export intersection type reflection
     *
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


    /**
     * Get container provider class
     */
    public function getProviderClass(): string
    {
        return $this->providerClass;
    }

    /**
    * Get facade proxy class
    */
    public function getProxyClass(): string
    {
        return $this->proxyClass;
    }

    /**
     * Get bind target
     */
    public function getTarget(): Proxy
    {
        if ($this->target === null) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->proxyClass . ' has not been bound to target yet',
                null,
                $this
            );
        }

        return $this->target;
    }


    /**
     * Get plugins
     *
     * @return array<string, Plugin>
     */
    public function getPlugins(): array
    {
        if ($this->plugins === null) {
            throw Exceptional::Runtime(
                'Proxy ' . $this->proxyClass . ' has not been bound to target yet',
                null,
                $this
            );
        }

        return $this->plugins;
    }

    /**
     * Get plugin names
     *
     * @return array<string>
     */
    public function getPluginNames(): array
    {
        return array_keys($this->getPlugins());
    }

    /**
     * Has plugin by name
     */
    public function hasPlugin(
        string $name
    ): bool {
        return in_array($name, $this->getPluginNames());
    }



    /**
     * Find list of plugin names
     */
    private function scanPlugins(
        object $instance
    ): void {
        $this->plugins = [];

        $ref = new ReflectionClass($this->providerClass);
        $props = $ref->getProperties();

        foreach ($props as $property) {
            $pluginAttr = $property->getAttributes(Plugin::class);

            if (empty($pluginAttr)) {
                continue;
            }

            $plugin = $pluginAttr[0]->newInstance();
            $plugin->setProperty($property);

            $this->plugins[$plugin->getName()] = $plugin;
        }
    }



    /**
     * Load plugins from target
     */
    private function loadPlugins(): void
    {
        if ($this->target === null) {
            throw Exceptional::Setup('Target binding has not been created');
        }

        foreach ($this->getPlugins() as $name => $plugin) {
            // Define loader
            $loader = function () use ($name, $plugin) {
                if ($this->target === null) {
                    throw Exceptional::Setup('Target binding has not been created');
                }

                /** @var object $instance */
                $instance = $this->target::getVeneerProxyTargetInstance();
                return $this->target::$$name = $plugin->load($instance);
            };


            if ($plugin->isLazy()) {
                // Apply later
                $this->target::$$name = $wrapper = new PluginWrapper($loader);

                if ($plugin->acceptsWrapper()) {
                    /** @var object|null $instance */
                    $instance = $this->target::getVeneerProxyTargetInstance();

                    if ($instance !== null) {
                        $instance->{$name} = $wrapper;
                    }
                }
            } else {
                // Apply now
                $loader();
            }
        }
    }
}

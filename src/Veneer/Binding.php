<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

//declare(strict_types=1);

namespace DecodeLabs\Veneer;

use DecodeLabs\Exceptional;


use DecodeLabs\Veneer;
use DecodeLabs\Veneer\Plugin\Wrapper as PluginWrapper;

use Psr\Container\ContainerInterface;

use ReflectionClass;
use ReflectionMethod;
use ReflectionObject;

class Binding
{
    /**
     * @phpstan-var class-string
     */
    protected string $providerClass;

    /**
     * @phpstan-var class-string
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
     * @phpstan-param class-string $providerClass
     * @phpstan-param class-string $proxyClass
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
        $attributes = $ref->getAttributes(LazyLoad::class);
        return !empty($attributes);
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
    public function resolveDeferral(): void
    {
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
        $method->invoke($instance);

        // Load plugins
        $this->loadPlugins();
    }


    /**
     * Extract target object
     *
     * @return $this
     */
    public function bindInstance(?ContainerInterface $container): Binding
    {
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
     * @phpstan-param class-string $instanceClass
     */
    private function createBindingClass(string $instanceClass): Proxy
    {
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
     * @phpstan-param class-string $instanceClass
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
        $class = '';


        // Uses
        $uses[] = 'DecodeLabs\\Veneer\\Proxy';
        $uses[] = 'DecodeLabs\\Veneer\\ProxyTrait';
        $uses[] = $instName . ' as Inst';
        $wrapper = false;

        foreach ($plugins as $name => $plugin) {
            $uses[] = $plugin->getType() . ' as ' . ucfirst($name) . 'Plugin';
            $type = ucfirst($name) . 'Plugin';

            if ($plugin->isLazy()) {
                $wrapper = true;
                $type .= '|PluginWrapper';
            }

            $properties[$name] = 'public static ' . $type . ' $' . $name . ';';
        }

        if ($wrapper) {
            $uses[] = 'DecodeLabs\\Veneer\\Plugin\\Wrapper as PluginWrapper';
        }

        foreach ($uses as $use) {
            $class .= 'use ' . $use . ';' . "\n";
        }


        // Class structure
        $class .= "\n";

        if ($listMethods) {
            $class .= $this->listClassMethods($ref);
        }

        $class .=
            'class ' . $className . ' implements Proxy' . "\n" .
            '{' . "\n" .
            '    use ProxyTrait;' . "\n\n";


        // Constants
        $consts['VENEER'] = 'const VENEER = \'' . $this->proxyClass . '\';';
        $consts['VENEER_TARGET'] = 'const VENEER_TARGET = Inst::class;';

        foreach (array_keys($ref->getConstants()) as $key) {
            if ($key === 'VENEER') {
                continue;
            }

            $consts[$key] = 'const ' . $key . ' = Inst::' . $key . ';' . "\n";
        }

        $class .= '    ' . implode("\n    ", $consts) . "\n\n";


        // Properties
        $class .= '    ' . implode("\n    ", $properties) . "\n";
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
     * @phpstan-param ReflectionClass<T> $ref
     */
    private function listClassMethods(ReflectionClass $ref): string
    {
        $methods = [];

        foreach ($ref->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            $string = ' * @method static ';
            $name = $method->getName();

            if ($method->hasReturnType()) {
                $string .= $method->getReturnType() . ' ';
            }

            $string .= $name . '(';
            $params = [];

            foreach ($method->getParameters() as $parameter) {
                $param = '';

                if ($parameter->hasType()) {
                    $param .= $parameter->getType() . ' ';
                }

                if ($parameter->isVariadic()) {
                    $param .= '...';
                }

                $param .= '$' . $parameter->getName();

                if ($parameter->isDefaultValueAvailable()) {
                    $param .= ' = ' . var_export($parameter->getDefaultValue(), true);
                }

                $params[] = $param;
            }

            $string .= implode(', ', $params) . ')';
            $methods[$name] = $string;
        }

        if (empty($methods)) {
            return '';
        }

        return
            '/**' . "\n" .
            implode("\n", $methods) . "\n" .
            ' */' . "\n";
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
    public function hasPlugin(string $name): bool
    {
        return in_array($name, $this->getPluginNames());
    }



    /**
     * Find list of plugin names
     */
    private function scanPlugins(object $instance): void
    {
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
        foreach ($this->getPlugins() as $name => $plugin) {
            $loader = function () use ($name, $plugin) {
                if ($this->target === null) {
                    throw Exceptional::Setup('Target binding has not been created');
                }

                /** @var object $instance */
                $instance = $this->target::getVeneerProxyTargetInstance();
                return $this->target::$$name = $plugin->load($instance);
            };

            if ($plugin->isLazy()) {
                $this->target::$$name = new PluginWrapper($loader);
            } else {
                $loader();
            }
        }
    }
}

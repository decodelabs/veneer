# Veneer — Package Specification

> **Cluster:** `runtime`
> **Language:** `php`
> **Milestone:** `null`
> **Repo:** `https://github.com/decodelabs/veneer`
> **Role:** Class frontages

## Overview

### Purpose

Veneer provides automated static facades for PHP objects. It allows creating static frontages (facades) for commonly used functionality without sacrificing testability. Veneer automatically generates proxy classes that forward static method calls to instance methods, enabling convenient static access while maintaining the ability to swap implementations for testing.

Key features:
- **Static facades**: Create static frontages for any class
- **Automatic proxy generation**: Dynamically generates proxy classes at runtime
- **Plugin support**: Declare plugins as properties with `Plugin` attribute for expanded interfaces
- **Lazy instantiation**: Uses PHP 8.4 lazy objects and proxies for deferred instantiation
- **Container integration**: Integrates with PSR-11 containers and Pandora containers
- **IDE stubs**: Generate stub files for IDE autocompletion
- **Slingshot integration**: Optional dependency injection for complex instantiation

### Non-Goals

- Veneer does not provide dependency injection (see Slingshot for complex DI).
- It does not handle service location or service discovery.
- It does not provide static analysis tools (though it includes PHPStan extension).
- It does not handle method interception or AOP.
- It does not provide caching or performance optimization beyond basic binding caching.

## Role in the Ecosystem

### Cluster & Positioning

Veneer belongs to the **runtime** cluster, providing foundational facade capabilities for creating static interfaces to instance-based classes. It serves as a utility for improving developer ergonomics while maintaining testability.

### Usage Contexts

- **Static facades**: Creating convenient static interfaces for commonly used classes
- **Testability**: Maintaining ability to swap implementations in tests
- **Plugin architecture**: Extending classes with plugin properties
- **IDE support**: Generating stub files for autocompletion
- **Legacy code integration**: Providing static interfaces for existing instance-based code

## Public Surface

### Key Types

- **`Veneer`** (class): Main facade class providing static registration methods. Auto-registered as facade for `Manager`.

- **`Manager`** (class): Manager class handling binding registration and proxy management. Implements `ContainerProvider`. Provides global manager singleton.

- **`Binding`** (class): Binding class representing a provider-to-proxy mapping. Handles proxy creation, plugin loading, and instance management.

- **`Proxy`** (interface): Proxy interface defining methods for instance management and static method forwarding.

- **`ProxyTrait`** (trait): Trait providing default implementation of `Proxy` interface methods.

- **`Plugin`** (class): Attribute class for marking plugin properties. Defines plugin instantiation strategy (manual, auto, lazy).

- **`Plugin\Strategy`** (enum): Enum defining plugin instantiation strategies (Manual, Auto, Lazy).

- **`Plugin\Wrapper`** (class): Wrapper class for plugins that cannot use lazy proxies (internal classes, interfaces). Provides transparent access to wrapped plugin.

- **`ContainerProvider`** (interface): Interface for classes providing container access.

- **`Proxy\ClassGenerator`** (class): Class generator for creating proxy classes dynamically.

- **`Stub\Generator`** (class): Stub generator for creating IDE stub files.

- **`PHPStan\VeneerReflectionExtension`** (class): PHPStan reflection extension for static analysis.

### Main Entry Points

**Veneer (Static Facade):**
- `Veneer::register(string $providerClass, string $proxyClass): bool` — Register binding
- `Veneer::has(string $proxyClass): bool` — Check if binding exists
- `Veneer::replacePlugin(object $instance, string $name, mixed $plugin): void` — Replace plugin instance
- `Veneer::getBindings(bool $mount = false): array` — Get all bindings
- `Veneer::getBinding(string $name, bool $mount = false): ?Binding` — Get binding by name
- `Veneer::newStubGenerator(string $scanDir, string $stubDir): StubGenerator` — Create stub generator

**Manager:**
- `Manager::getGlobalManager(): Manager` — Get global manager instance
- `new Manager()` — Constructor (registers autoloader)
- `$manager->register(string $providerClass, string $proxyClass): bool` — Register binding
- `$manager->has(string $proxyClass): bool` — Check if binding exists
- `$manager->replacePlugin(object $instance, string $name, mixed $plugin): void` — Replace plugin instance
- `$manager->getBindings(bool $mount = false): array` — Get all bindings
- `$manager->getBinding(string $name, bool $mount = false): ?Binding` — Get binding by name
- `$manager->container` — PSR-11 container instance (public property)
- `$manager->setContainer(ContainerInterface $container): void` — Set container

**Binding:**
- `new Binding(string $providerClass, string $proxyClass)` — Constructor
- `$binding->getProviderClass(): string` — Get provider class name
- `$binding->getProxyClass(): string` — Get proxy class name
- `$binding->mount(ContainerProvider $containerProvider): Binding` — Mount binding (create proxy and instance)
- `$binding->hasInstance(): bool` — Check if instance exists
- `$binding->getInstance(): ?object` — Get bound instance
- `$binding->getProxy(): Proxy` — Get proxy instance
- `$binding->getPlugins(): array` — Get all plugins
- `$binding->getPluginNames(): array` — Get plugin names
- `$binding->getPlugin(string $name): ?Plugin` — Get plugin by name
- `$binding->hasPlugin(string $name): bool` — Check if plugin exists

**Proxy Interface:**
- `Proxy::_setVeneerInstance(object $instance): void` — Set bound instance
- `Proxy::_getVeneerInstance(): ?object` — Get bound instance
- `Proxy::__callStatic(string $name, array $args): mixed` — Forward static method calls

**Plugin Attribute:**
- `new Plugin(bool $auto = false, bool $lazy = false, ?string $type = null)` — Constructor
- `$plugin->name` — Plugin property name (readonly property)
- `$plugin->type` — Plugin type class name (readonly property)
- `$plugin->instanceType` — Plugin instance type class name (readonly property)
- `$plugin->property` — Reflection property (public property)
- `$plugin->strategy` — Instantiation strategy (public property)
- `$plugin->auto` — Auto instantiation flag (readonly property)
- `$plugin->lazy` — Lazy instantiation flag (readonly property)
- `$plugin->reflection` — Reflection class (readonly property)
- `$plugin->isInstantiable(): bool` — Check if plugin is instantiable
- `$plugin->requiresWrapper(): bool` — Check if plugin requires wrapper
- `$plugin->load(object $instance, ContainerProvider $containerProvider, ?object $proxy = null): object` — Load plugin instance

**Plugin\Strategy Enum:**
- `Strategy::Manual` — Manual instantiation
- `Strategy::Auto` — Auto instantiation
- `Strategy::Lazy` — Lazy instantiation
- `$strategy->isManual(): bool` — Check if manual
- `$strategy->isEagerAuto(): bool` — Check if eager auto
- `$strategy->isAuto(): bool` — Check if auto (includes lazy)
- `$strategy->isLazy(): bool` — Check if lazy

**Plugin\Wrapper:**
- `new Wrapper(callable $loader)` — Constructor
- `$wrapper->__get(string $name): mixed` — Get property
- `$wrapper->__call(string $name, array $args): mixed` — Call method
- `$wrapper->getVeneerPlugin(): object` — Get wrapped plugin instance
- `$wrapper->offsetSet(mixed $offset, mixed $value): void` — ArrayAccess set
- `$wrapper->offsetGet(mixed $offset): mixed` — ArrayAccess get
- `$wrapper->offsetExists(mixed $offset): bool` — ArrayAccess exists
- `$wrapper->offsetUnset(mixed $offset): void` — ArrayAccess unset
- `$wrapper->getIterator(): Traversable` — IteratorAggregate getIterator
- `$wrapper->__toString(): string` — Stringable toString

**ContainerProvider Interface:**
- `ContainerProvider::$container` — PSR-11 container instance (readonly property)

**Proxy\ClassGenerator:**
- `new ClassGenerator(Binding $binding, ?string $instanceClass = null)` — Constructor
- `$generator->generate(?string $namespace = null, bool $withMethods = false): string` — Generate proxy class code

**Stub\Generator:**
- `new Generator(string $scanDir, string $stubDir)` — Constructor
- `$generator->scan(): array` — Scan directory for bindings
- `$generator->generate(Binding $binding): void` — Generate stub file for binding

## Dependencies

### Decode Labs

- **`decodelabs/exceptional`**: Required. Used for exception handling throughout the package.

### External

- **PHP**: See `composer.json` for supported PHP versions.
- **`psr/container`**: Required. PSR-11 container interface (^2.0.2).

### Optional

- **`decodelabs/slingshot`**: Suggested. Used for complex plugin instantiation when constructors have required parameters. Detected at runtime if installed, used for dependency injection.
- **`decodelabs/pandora`**: Detected at runtime if installed, used for container binding integration.

## Behaviour & Contracts

### Invariants

- Global manager created lazily on first access.
- Bindings registered before proxy class is loaded.
- Proxy classes generated dynamically at mount time.
- Proxy classes implement `Proxy` interface and use `ProxyTrait`.
- Plugin properties must have `Plugin` attribute.
- Plugin instantiation strategies: Manual (constructor), Auto (eager), Lazy (deferred).
- Lazy plugins use PHP 8.4 lazy proxies for instantiable classes.
- Non-instantiable plugins use `PluginWrapper` for transparent access.
- Container integration optional (PSR-11 or Pandora).
- Binding caching enabled during PHPStan runs.

### Input & Output Contracts

**Registration:**
- `register()` accepts provider class name and proxy class name.
- Returns `false` if proxy class already exists.
- Returns `true` if registration successful.
- Binding stored in manager's bindings array.

**Binding Mounting:**
- `mount()` creates proxy class and instance.
- Instance obtained from container if available.
- If not in container, instance created via reflection.
- Uses lazy ghost for deferred instantiation if class has constructor.
- Constructor dependencies resolved via Slingshot if available.
- Plugins loaded after instance creation.
- Proxy class generated dynamically.
- Instance bound to proxy via `_setVeneerInstance()`.

**Proxy Generation:**
- Proxy class generated in namespace `DecodeLabs\Veneer\Binding`.
- Proxy class implements `Proxy` interface.
- Proxy class uses `ProxyTrait` for default implementation.
- Proxy class includes static properties for plugins.
- Proxy class includes constants from provider class.
- Proxy class aliased to proxy class name via `class_alias()`.

**Plugin Loading:**
- Plugins scanned via reflection on provider class.
- Plugin properties identified by `Plugin` attribute.
- Plugin type determined from property type.
- Plugin instantiation strategy determined from attribute parameters.
- Manual plugins: must be instantiated in constructor.
- Auto plugins: instantiated eagerly at mount time.
- Lazy plugins: instantiated on first access.
- Lazy plugins use PHP 8.4 `newLazyProxy()` for instantiable classes.
- Non-instantiable plugins use `PluginWrapper`.

**Static Method Forwarding:**
- Static method calls forwarded via `__callStatic()`.
- Method calls forwarded to bound instance.
- Arguments passed through unchanged.
- Return value returned from instance method.

**Plugin Access:**
- Plugin properties accessible as static properties on proxy.
- Plugin access triggers lazy loading if needed.
- Plugin wrappers resolve to actual plugin on access.
- Plugin instances synchronized between proxy and instance.

**Container Integration:**
- Container set via `setContainer()` or `container` property.
- Container checked for provider instance during mount.
- Pandora containers bind instances automatically.
- Container binding happens after instance creation.

**Stub Generation:**
- Stub generator scans directory for bindings.
- Root files loaded to discover registrations.
- Stub files generated with full method signatures.
- Stub files placed in specified directory structure.

## Error Handling

- **Proxy class exists**: `register()` returns `false` if proxy class already exists.
- **No instance bound**: `__callStatic()` throws `Runtime` exception if no instance bound.
- **Plugin not found**: `replacePlugin()` throws `Runtime` exception if plugin not found.
- **Binding not found**: `getBindingForInstance()` throws `Runtime` exception if binding not found.
- **Manual plugin not instantiated**: `load()` throws `Setup` exception if manual plugin not instantiated.
- **Cannot instantiate plugin**: `instantiate()` throws `Setup` exception if plugin not instantiable.
- **Slingshot required**: Throws `ComponentUnavailable` exception if constructor dependencies require Slingshot but not available.
- **No target bound**: `getProxy()` throws `Runtime` exception if proxy not bound yet.
- **Plugin property not defined**: Plugin property access throws `Runtime` exception if property not set.
- **Plugin property already defined**: Plugin property set throws `Runtime` exception if already defined.
- **ArrayAccess not implemented**: Wrapper throws `Runtime` exception if plugin doesn't implement ArrayAccess.
- **Traversable not implemented**: Wrapper throws `Runtime` exception if plugin doesn't implement Traversable.
- **Stringable not implemented**: Wrapper throws `Runtime` exception if plugin doesn't implement Stringable.

## Configuration & Extensibility

### Custom Manager

Create custom manager instance:

```php
use DecodeLabs\Veneer\Manager;

$manager = new Manager();
$manager->register(MyClass::class, MyFacade::class);
```

### Container Integration

Set container for dependency resolution:

```php
use DecodeLabs\Veneer\Manager;
use Psr\Container\ContainerInterface;

$manager = Manager::getGlobalManager();
$manager->setContainer($container);
```

### Plugin Strategies

Define plugins with different strategies:

```php
use DecodeLabs\Veneer\Plugin;

class MyClass
{
    #[Plugin] // Manual - must instantiate in constructor
    public MyPlugin $plugin;

    #[Plugin(auto: true)] // Auto - instantiated at mount time
    public MyPlugin $autoPlugin;

    #[Plugin(lazy: true)] // Lazy - instantiated on first access
    public MyPlugin $lazyPlugin;

    public function __construct()
    {
        $this->plugin = new MyPlugin();
    }
}
```

### Property Hooks

Use property hooks with plugins:

```php
use DecodeLabs\Veneer\Plugin;

class MyClass
{
    #[Plugin]
    protected(set) MyPlugin $plugin {
        get => $this->plugin ??= new MyPlugin();
    }
}
```

### Replacing Plugins

Replace plugin instances:

```php
use DecodeLabs\Veneer\Veneer;

$instance = new MyClass();
Veneer::replacePlugin($instance, 'plugin', $newPlugin);
```

### Stub Generation

Generate IDE stubs:

```php
use DecodeLabs\Veneer\Manager;

$generator = Manager::getGlobalManager()->newStubGenerator(
    scanDir: '/path/to/src',
    stubDir: '/path/to/stubs'
);

$bindings = $generator->scan();
foreach ($bindings as $binding) {
    $generator->generate($binding);
}
```

## Interactions with Other Packages

- **Slingshot**: Detected at runtime if installed, used for dependency injection when constructors have required parameters.
- **Pandora**: Detected at runtime if installed, used for container binding integration.
- **Exceptional**: Used for exception handling throughout the package.
- **PSR Container**: Used for container interface (PSR-11).

## Usage Examples

### Basic Registration

```php
use DecodeLabs\Veneer;
use Some\Random\Library\MyThing;
use App\CoolThing;

Veneer::register(
    MyThing::class, // active object class
    CoolThing::class // frontage class
);

// Use static facade
use App\CoolThing;

CoolThing::doAThing();
```

### Plugin Declaration

```php
use DecodeLabs\Veneer\Plugin;

class MyThing
{
    #[Plugin]
    public MyPlugin $plugin;

    #[Plugin(auto: true)]
    public MyPlugin $autoPlugin;

    #[Plugin(lazy: true)]
    public MyPlugin $lazyPlugin;

    public function __construct()
    {
        $this->plugin = new MyPlugin();
    }
}

class MyPlugin
{
    public function doAThing(): string
    {
        return 'Hello from plugin';
    }
}

// Use plugins
MyThing::$plugin->doAThing(); // Hello from plugin
MyThing::$autoPlugin->doAThing(); // Hello from plugin
MyThing::$lazyPlugin->doAThing(); // Hello from plugin
```

### Property Hooks

```php
use DecodeLabs\Veneer\Plugin;

class MyThing
{
    #[Plugin]
    protected(set) MyPlugin $plugin {
        get => $this->plugin ??= new MyPlugin();
    }
}
```

### Container Integration

```php
use DecodeLabs\Veneer\Manager;
use Psr\Container\ContainerInterface;

$manager = Manager::getGlobalManager();
$manager->setContainer($container);

Veneer::register(MyClass::class, MyFacade::class);
// Instance will be obtained from container if available
```

### Replacing Plugins

```php
use DecodeLabs\Veneer\Veneer;

$instance = new MyClass();
$newPlugin = new MyPlugin();

Veneer::replacePlugin($instance, 'plugin', $newPlugin);
// Plugin replaced in both instance and proxy
```

### Stub Generation

```php
use DecodeLabs\Veneer\Manager;

$generator = Manager::getGlobalManager()->newStubGenerator(
    scanDir: __DIR__ . '/src',
    stubDir: __DIR__ . '/stubs'
);

$bindings = $generator->scan();
foreach ($bindings as $binding) {
    $generator->generate($binding);
}
```

## Implementation Notes (for Contributors)

### Manager Implementation

- Manager uses singleton pattern for global instance.
- Autoloader registered in constructor for lazy proxy loading.
- Bindings stored in array keyed by proxy class name.
- Container integration optional (PSR-11 or Pandora).

### Binding Implementation

- Binding created on registration.
- Mounting deferred until proxy class accessed.
- Instance obtained from container if available.
- Instance created via reflection if not in container.
- Lazy ghost used for deferred instantiation.
- Constructor dependencies resolved via Slingshot if available.
- Proxy class generated dynamically via `ClassGenerator`.
- Plugins loaded after instance creation.

### Proxy Generation

- Proxy class generated in `DecodeLabs\Veneer\Binding` namespace.
- Proxy class implements `Proxy` interface.
- Proxy class uses `ProxyTrait` for default implementation.
- Proxy class includes static properties for plugins.
- Proxy class includes constants from provider class.
- Proxy class aliased to proxy class name.
- Binding caching enabled during PHPStan runs.

### Plugin Loading

- Plugins scanned via reflection on provider class.
- Plugin properties identified by `Plugin` attribute.
- Plugin type determined from property type.
- Plugin instantiation strategy determined from attribute.
- Manual plugins: must be instantiated in constructor.
- Auto plugins: instantiated eagerly at mount time.
- Lazy plugins: instantiated on first access.
- Lazy plugins use PHP 8.4 `newLazyProxy()` for instantiable classes.
- Non-instantiable plugins use `PluginWrapper`.

### Lazy Instantiation

- PHP 8.4 lazy objects used for deferred instantiation.
- Lazy proxies used for plugin instantiation.
- Ghost objects used for instance creation.
- Wrapper objects used for non-instantiable plugins.

### Container Integration

- Container checked for provider instance during mount.
- Pandora containers bind instances automatically.
- Container binding happens after instance creation.
- Container set via `setContainer()` or `container` property.

### Stub Generation

- Stub generator scans directory for bindings.
- Root files loaded to discover registrations.
- Stub files generated with full method signatures.
- Stub files placed in specified directory structure.
- Stub files include full type information for IDE.

## Testing & Quality

**Current Status:**
- Code quality: 4.5/5
- README quality: 3/5
- Documentation: 0/5 (no formal docs yet)
- Tests: 0/5 (no test suite yet)

**Testing Considerations:**
- Manager should be tested for:
  - Registration (success and failure)
  - Binding retrieval
  - Container integration
  - Plugin replacement

- Binding should be tested for:
  - Mounting (with and without container)
  - Instance creation
  - Proxy generation
  - Plugin loading (manual, auto, lazy)
  - Lazy instantiation

- Proxy should be tested for:
  - Static method forwarding
  - Instance management
  - Plugin access

- Plugin should be tested for:
  - Manual instantiation
  - Auto instantiation
  - Lazy instantiation
  - Wrapper usage

- ClassGenerator should be tested for:
  - Proxy class generation
  - Method signature generation
  - Type export
  - Constant copying

- StubGenerator should be tested for:
  - Directory scanning
  - Binding discovery
  - Stub file generation

- Edge cases should be tested for:
  - Missing container
  - Missing Slingshot (with required dependencies)
  - Non-instantiable plugins
  - Internal classes
  - Interfaces
  - Virtual property hooks
  - Plugin synchronization

## Roadmap & Future Ideas

- **Performance optimization**: Caching and optimization for proxy generation
- **Better error messages**: More detailed error messages for debugging
- **Method interception**: Support for method interception and AOP
- **Static property support**: Support for static properties in facades
- **Better IDE support**: Enhanced stub generation and IDE integration
- **Proxy validation**: Validation of proxy classes and bindings

## References

- Package repository: https://github.com/decodelabs/veneer
- Composer package: https://packagist.org/packages/decodelabs/veneer
- Related packages:
  - `decodelabs/slingshot` — Dependency injection for complex instantiation
  - `decodelabs/pandora` — Container binding integration
  - `decodelabs/exceptional` — Exception handling
- PHP 8.4 features:
  - Lazy objects: https://www.php.net/manual/en/language.oop5.lazy-objects.php
  - Property hooks: https://www.php.net/manual/en/language.oop5.properties.php#language.oop5.properties.hooks


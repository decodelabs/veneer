# Veneer

[![Latest Version](https://img.shields.io/packagist/v/decodelabs/veneer.svg?style=flat-square)](https://packagist.org/packages/decodelabs/veneer)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/veneer.svg?style=flat-square)](https://packagist.org/packages/decodelabs/veneer)
[![Build Status](https://img.shields.io/travis/decodelabs/veneer/develop.svg?style=flat-square)](https://travis-ci.org/decodelabs/veneer)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/veneer?style=flat-square)](https://packagist.org/packages/decodelabs/veneer)

Create automated static Facades for your PHP objects via any PSR-11 container.

## Install

```bash
composer require decodelabs/veneer
```

## Usage
Say you have a common library class you use regularly:

```php
namespace Some\Random\Library
{
    // This is a library class you use regularly
    class MyThing {
        public function doAThing() {
            echo 'Done!';
        }
    }
}
```

You can bind a global name to an instance of the class to be used statically.
Either via a PSR 11 container:

```php
namespace App\Setup
{
    // This is your environment setup code
    use DecodeLabs\Veneer\Manager;
    use DecodeLabs\Veneer\Listener\AutoLoad as Listener;
    use Some\Random\Library\MyThing;

    // Use whatever PSR 11 container you want
    $psr11Container = new WhateverContainer();

    // Create and bind your main listener
    $listener = new Listener();
    $psr11Container->bind(Listener::class, $listener);

    // Bind your instance to your container
    $psr11Container->bind('thing', new MyThing());

    // Create a veneer manager
    $manager = new Manager($psr11Container);
    $psr11Container->bind(Manager::class, $manager);
    $listener->registerManager($manager);

    // Bind the name "CoolThing" to your instance in the container
    $manager->bindGlobalFacade('CoolThing', 'thing');
}


namespace Some\Other\Code
{
    // Your general userland code
    // No need to import any namespaces for global facades -
    // they are loaded into the current namespace automatically
    CoolThing::doAThing();

    // Logically equivalent to: (without the fuss)
    $psr11Container->get('thing')->doAThing();
}
```

...or directly, without a container, using the class name as the instance key:

```php
namespace App\Setup
{
    // This is your environment setup code
    use DecodeLabs\Veneer\Register;
    use DecodeLabs\Veneer\Manager;
    use DecodeLabs\Veneer\Listener\AutoLoad as Listener;
    use Some\Random\Library\MyThing;

    // Create a veneer manager
    $manager = new Manager();
    Register::getGlobalListener()->registerManager($manager);

    // Bind the name "CoolThing" to your instance in the container
    $manager->bindGlobalFacade('CoolThing', MyThing::class);
}

namespace Some\Other\Code
{
    // Your general userland code
    CoolThing::doAThing();
}
```


## Extended functionality

Implement <code>DecodeLabs\Veneer\FacadeTarget</code> to access advanced Facade features.

```php
namespace My\Library
{
    use DecodeLabs\Veneer\Manager;
    use DecodeLabs\Veneer\FacadeTarget;
    use DecodeLabs\Veneer\FacadeTargetTrait;

    class MyThing implements FacadeTarget {
        use FacadeTargetTrait;

        /**
         * Optionally define custom binding method
         * Default is global as below
         */
        public static function bindFacade(Manager $manager, string $name, string $class): void
        {
            $manager->bindGlobalFacade($name, $class);
        }
    }
}
```

### Binding shortcuts

Shortcut the facade binding process with a static call directly to the target class:

```php
// Register as global facade
\My\Library\MyThing::registerFacade();
```


### Plugins

Unfortunately PHP still doesn't have <code>\__getStatic()</code> yet so we have to statically declare plugin names at binding time, but they're still useful for creating more expansive interfaces.

Define two methods on your <code>FacadeTarget</code>


```php
namespace My\Library
{
    use DecodeLabs\Veneer\FacadeTarget;
    use DecodeLabs\Veneer\FacadeTargetTrait;
    use DecodeLabs\Veneer\FacadeTargetPlugin;

    class MyThing implements FacadeTarget {

        use FacadeTargetTrait;

        public function getFacadePluginNames(): array {
            // Return the list of plugin names to be accessed from the facade
            return [
                'plugin1',
                'plugin2'
            ];
        }

        public function loadFacadePlugin(string $name): FacadePlugin {
            // Load plugin object (must implement FacadePlugin)

            // This is just a quick example using anonymous classes:
            return new class($name) implements FacadeTarget {

                public $name;

                public function __construct(string $name) {
                    $this->name = $name;
                }

                public function doAThing() {
                    echo 'Hello from '.$this->name;
                }
            }
        }
    }
}

namespace Some\Other\Code
{
    MyThing::$plugin1->doAThing(); // Hello from plugin1
}
```


## Licensing
Veneer is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.

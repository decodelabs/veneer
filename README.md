# Veneer
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
    use Some\Random\Library\MyThing;

    // Use whatever PSR 11 container you want
    $psr11Container = new WhateverContainer();

    // Bind your instance to your container
    $psr11Container->bind('thing', new MyThing());

    // Create a veneer manager
    $manager = new Manager($psr11Container);

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

...or directly, without, using the class name as the instance key:

```php
namespace App\Setup
{
    // This is your environment setup code
    use DecodeLabs\Veneer\Manager;
    use Some\Random\Library\MyThing;

    // Create a veneer manager
    $manager = new Manager();

    // Bind the name "CoolThing" to your instance in the container
    $manager->bindGlobalFacade('CoolThing', MyThing::class);
}

namespace Some\Other\Code
{
    // Your general userland code
    CoolThing::doAThing();
}
```

Just make sure you keep track of the Manager object and ensure there's only one instance in use.


## Extended functionality

Implement <code>DecodeLabs\Veneer\FacadeTarget</code> to access advanced Facade features.

```php
namespace My\Library
{
    use DecodeLabs\Veneer\FacadeTarget;
    use DecodeLabs\Veneer\FacadeTargetTrait;

    class MyThing implements FacadeTarget {
        use FacadeTargetTrait;
    }
}
```

### Binding shortcuts

Shortcut the facade binding process with a static call directly to the target class:

```php
// Register as global facade
\My\Library\MyThing::registerFacade($psr11Container ?? null, $veneerManager ?? null);
\My\Library\MyThing::registerGlobalFacade($psr11Container ?? null, $veneerManager ?? null);

// Register in root namespace only
\My\Library\MyThing::registerRootFacade($psr11Container ?? null, $veneerManager ?? null);

// Register in current local namespace
\My\Library\MyThing::registerLocalFacade($psr11Container ?? null, $veneerManager ?? null);
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

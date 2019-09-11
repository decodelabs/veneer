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

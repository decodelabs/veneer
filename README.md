# Veneer

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/veneer?style=flat-square)](https://packagist.org/packages/decodelabs/veneer)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/veneer.svg?style=flat-square)](https://packagist.org/packages/decodelabs/veneer)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/veneer.svg?style=flat-square)](https://packagist.org/packages/decodelabs/veneer)
[![Build Status](https://img.shields.io/travis/com/decodelabs/veneer/main.svg?style=flat-square)](https://travis-ci.com/decodelabs/veneer)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/veneer?style=flat-square)](https://packagist.org/packages/decodelabs/veneer)

Create automated static frontages for your PHP objects.

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


You can bind a static, automatically generated frontage by:

```php
namespace App\Setup
{
    // This is your environment setup code
    use DecodeLabs\Veneer;
    use Some\Random\Library\MyThing;
    use App\CoolThing;

    Veneer::register(
        MyThing::class, // active object class
        MyThingFrontage::class // frontage class
    );
}

namespace Some\Other\Code
{
    use App\CoolThing;

    // Your general userland code
    CoolThing::doAThing();
}
```


### Plugins

Unfortunately PHP still doesn't have <code>\__getStatic()</code> yet so we have to statically declare plugin names at binding time, but they're still useful for creating more expansive interfaces.

Define two methods on your <code>FacadeTarget</code>


```php
namespace My\Library
{
    use DecodeLabs\Veneer\Plugin;
    use DecodeLabs\Veneer\Plugin\Provider;
    use DecodeLabs\Veneer\Plugin\ProviderTrait;

    class MyThing implements Provider {

        use ProviderTrait;

        public function getVeneerPluginNames(): array {
            // Return the list of plugin names to be accessed from the facade
            return [
                'plugin1',
                'plugin2'
            ];
        }

        public function loadVeneerPlugin(string $name): Plugin {
            // Load plugin object (must implement Plugin)

            // This is just a quick example using anonymous classes:
            return new class($name) implements Plugin {

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
    use My\Library\MyThing;

    MyThing::$plugin1->doAThing(); // Hello from plugin1
}
```


## Licensing
Veneer is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.

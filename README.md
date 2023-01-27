# Veneer

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/veneer?style=flat)](https://packagist.org/packages/decodelabs/veneer)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/veneer.svg?style=flat)](https://packagist.org/packages/decodelabs/veneer)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/veneer.svg?style=flat)](https://packagist.org/packages/decodelabs/veneer)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/veneer/integrate.yml?branch=develop)](https://github.com/decodelabs/veneer/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/veneer?style=flat)](https://packagist.org/packages/decodelabs/veneer)

### Create automated static frontages for your PHP objects.

Use Veneer to provide easy access to your most commonly used functionality without sacrificing testability.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---

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

Define plugins as properties on your <code>FacadeTarget</code> with a <code>Plugin</code> attribute, include <code>LazyLoad</code> attribute too if it doesn't need to be loaded straight away.


```php
namespace My\Library
{
    use DecodeLabs\Veneer\Plugin;
    use DecodeLabs\Veneer\LazyLoad;

    class MyThing {

        #[Plugin]
        #[LazyLoad]
        public MyPlugin $plugin;
    }


    class MyPlugin
    {
        public function doAThing(): string {
            return 'Hello from plugin1';
        }
    }
}

namespace Some\Other\Code
{
    use My\Library\MyThing;

    MyThing::$plugin->doAThing(); // Hello from plugin1
}
```


## Licensing
Veneer is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.

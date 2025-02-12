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
namespace Some\Random\Library;

// This is a library class you use regularly
class MyThing
{
    public function doAThing() {
        echo 'Done!';
    }
}
```


You can bind a static, automatically generated frontage by:

```php
namespace App\Setup;

// This is your environment setup code
use DecodeLabs\Veneer;
use Some\Random\Library\MyThing;
use App\CoolThing;

Veneer::register(
    MyThing::class, // active object class
    CoolThing::class // frontage class
);




namespace Some\Other\Code;

use App\CoolThing;

// Your general userland code
CoolThing::doAThing();
```


### Plugins

Unfortunately PHP still doesn't have <code>\__getStatic()</code> yet so we have to statically declare plugin names at binding time, but they're still useful for creating more expansive interfaces.

Define plugins as properties on your <code>FacadeTarget</code> with a <code>Plugin</code> attribute. By default, plugins require manual instantiation in the constructor, however you can flag it as <code>auto</code> to have it automatically built at bind time, or <code>lazy</code> if it doesn't need to be loaded straight away.


```php
namespace My\Library
{
    use DecodeLabs\Veneer\Plugin;

    class MyThing {

        #[Plugin]
        public MyPlugin $plugin;

        #[Plugin(auto: true)]
        public MyPlugin $autoPlugin;

        #[Plugin(lazy: true)]
        public MyPlugin $lazyPlugin;

        public function __construct() {
            $this->plugin = new MyPlugin();
        }
    }


    class MyPlugin
    {
        public function doAThing(): string {
            return 'Hello from plugin';
        }
    }
}

namespace Some\Other\Code
{
    use My\Library\MyThing;

    MyThing::$plugin->doAThing(); // Hello from plugin
    MyThing::$autoPlugin->doAThing(); // Hello from plugin
    MyThing::$lazyPlugin->doAThing(); // Hello from plugin
}
```

Note, if your target class has a constructor with required parameters, you will need to add <code>decodelabs/slingshot</code> to your project to allow Veneer to instantiate it.

Lazy instantiation uses the new ghost and proxy functionality in PHP8.4 and will only instantiate the plugin when it is first accessed. Due to the limitations of lazy objects in PHP, you cannot create a lazy proxy for internal classes so you may find that plugins are referenced with a transparent <code>Plugin\Wrapper</code> class which resolves to the actual plugin instance when accessed. This usually isn't an issue unless you try to pass a plugin instance to a function that expects a specific class type, directly from the proxy. In these cases you should return the plugin instance from a method on the target class.


### Property Hooks

PHP 8.4 property hooks can be used in combination with Plugins, however be aware that they will conflict with auto and lazy instantiation. Hooks defined with the structure below will effectively act like a lazy loaded plugin, however with the additional benefits of being able to control how it is instantiated rather than relying on Slingshot.


```php
namespace My\Library
{
    use DecodeLabs\Veneer\Plugin;

    class MyThing {

        #[Plugin]
        protected(set) MyPlugin $plugin {
            get => $this->plugin ??= new MyPlugin();
        }
    }
}
```

Hooks _can_ be virtual (ie, don't require a value backed property), however the proxy will reference the first instantation of a virtual hook and your plugin instances will likely go out of sync.

Note, the <code>protected(set)</code> visibility in the example; it is not a requirement, but it is recommended to prevent direct write access to the property. If you need to replace a plugin instance, you should do so via <code>Veneer::replacePlugin(\$providerInstance, \'propertyName\', \$newPlugin)</code>. This allows Veneer to update the plugin in the static frontage proxy as well as the target instance.

## Licensing
Veneer is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.

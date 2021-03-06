## v0.7.5 (2021-05-11)
* Added stub generator system

## v0.7.3 (2021-05-05)
* Disabled strict types for ProxyTrait __callStatic

## v0.7.2 (2021-04-16)
* Disabled strict typing in Binding class

## v0.7.1 (2021-04-07)
* Updated for max PHPStan conformance

## v0.7.0 (2021-03-18)
* Enabled PHP8 testing

## v0.6.4 (2020-10-06)
* Added PSR11 container accessors
* Applied full PSR12 standards
* Added PSR12 check to Travis build

## v0.6.3 (2020-10-05)
* Improved readme
* Updated PHPStan

## v0.6.2 (2020-10-04)
* Updated binding handling for PHPStan

## v0.6.1 (2020-10-04)
* Fixed cached binding support for PHPStan

## v0.6.0 (2020-10-04)
* Restructured main library classes
* Simplified proxy registration
* Removed Veneer facade
* Removed support for auto namespace loading
* Removed listener structure
* Normalised plugin interface
* Added binding cache support for PHPStan

## v0.5.5 (2020-10-02)
* Updated glitch-support

## v0.5.4 (2020-10-02)
* Removed Glitch dependency

## v0.5.3 (2020-09-30)
* Switched to Exceptional for exception generation

## v0.5.2 (2020-09-25)
* Switched to Glitch Dumpable interface

## v0.5.1 (2020-09-24)
* Updated Composer dependency handling

## v0.5.0 (2019-10-26)
* Added namespace white / black list
* Added facade instance getter
* Added shared Veneer context facade
* Removed class_exists workaround
* Improved PHPStan setup

## v0.4.1 (2019-10-15)
* Updated binding dump handling
* Added PHPStan support
* Bugfixes from PHPStan max scan

## v0.4.0 (2019-09-26)
* Fixed multiple plugin binding namespacing
* Added dump handling to plugins
* Added manual class alias loader on binding instantiation

## v0.3.0 (2019-09-24)
* Moved autoload code to Listener structure
* Created singleton Register for default Listener
* Lazy load bindings

## v0.2.0 (2019-09-13)
* Normalised interfaces and traits
* Added Facade plugin binding mechanism

## v0.1.0
* Created initial Facade generator structure

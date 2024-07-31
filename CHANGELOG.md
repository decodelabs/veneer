* Made all bindings lazy by default

## v0.11.1 (2024-07-17)
* Improved non-Slingshot object instantiation

## v0.11.0 (2024-07-17)
* Made Slingshot optional

## v0.10.25 (2024-04-29)
* Fixed re-adding early-bound instances to container

## v0.10.24 (2023-11-14)
* Fixed Slingshot dependency

## v0.10.23 (2023-11-10)
* Switched to Slingshot for function invocation
* Moved to PHP8.1 minimum

## v0.10.22 (2023-11-09)
* Fixed handling of private and protected class constants

## v0.10.21 (2023-11-08)
* Improved deferred binding argument handling

## v0.10.20 (2023-11-07)
* Added Container resolution for deferred constructor arguments

## v0.10.19 (2023-10-05)
* Added ArrayAccess support to Plugin Wrapper
* Added IteratorAggregate support to Plugin Wrapper

## v0.10.18 (2023-09-26)
* Converted phpstan doc comments to generic

## v0.10.17 (2023-01-27)
* Added slashes to stub class name strings

## v0.10.16 (2022-11-28)
* Ensure protected constructors are invocable in bindings

## v0.10.15 (2022-11-28)
* Added replacePlugin helper
* Simplified bin dir handling

## v0.10.14 (2022-11-22)
* Properly fixed binding ref generation

## v0.10.13 (2022-11-22)
* Fixed binding ref generation

## v0.10.12 (2022-11-22)
* Fixed use alias mapping for multiple refs
* Migrated to use effigy in CI workflow
* Fixed PHP8.1 testing

## v0.10.11 (2022-09-29)
* Turned off strict mode in PuginWrapper

## v0.10.10 (2022-09-29)
* Fixed stub generator

## v0.10.9 (2022-09-29)
* Auto-wrap plugins if type accepted
* Removed Atlas and Terminus dependencies for stubs

## v0.10.8 (2022-09-28)
* Added ensurePlugin() helper for lazy loaders

## v0.10.7 (2022-09-27)
* Fixed static type ref handling

## v0.10.6 (2022-09-27)
* Fixed nullable type export
* Updated composer check script

## v0.10.5 (2022-09-27)
* Fixed plugin wrapper type inference

## v0.10.4 (2022-09-27)
* Improved stub generation

## v0.10.3 (2022-09-27)
* Export full method defs in Stubs

## v0.10.2 (2022-09-27)
* Fixed stub generator scanner

## v0.10.1 (2022-09-27)
* Improved stub generator

## v0.10.0 (2022-09-27)
* Converted Plugin handling to use Attributes
* Enabled direct loading of Plugins
* Removed trailing space from stubs
* Updated CI environment

## v0.9.2 (2022-08-23)
* Added concrete types to all members

## v0.9.1 (2022-08-23)
* Updated Stub Generator testing

## v0.9.0 (2022-08-22)
* Removed PHP7 compatibility
* Moved Stub Generator out of PHPStan scope
* Updated PSR Container interface to v2
* Updated ECS to v11
* Updated PHPUnit to v9

## v0.8.5 (2022-03-25)
* Allow disabling deferrals for PHPStan

## v0.8.4 (2022-03-24)
* Defer target construction until after binding

## v0.8.3 (2022-03-24)
* Added lazy loading support

## v0.8.2 (2022-03-09)
* Transitioned from Travis to GHA
* Updated PHPStan and ECS dependencies

## v0.8.1 (2021-10-20)
* Fixed stub generator

## v0.8.0 (2021-10-20)
* Simplified binding structure

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

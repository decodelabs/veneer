# Changelog

All notable changes to this project will be documented in this file.<br>
The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

### Unreleased
- Applied ECS formatting to all code

---

### [v0.12.8](https://github.com/decodelabs/veneer/commits/v0.12.8) - 6th June 2025

- Removed dump handling dependencies
- Upgraded Exceptional to v0.6
- Removed Slingshot from deps list

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.12.7...v0.12.8)

---

### [v0.12.7](https://github.com/decodelabs/veneer/commits/v0.12.7) - 9th April 2025

- Fixed Plugin type handling

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.12.6...v0.12.7)

---

### [v0.12.6](https://github.com/decodelabs/veneer/commits/v0.12.6) - 18th February 2025

- Avoid instantiating instance while checking plugin initialization

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.12.5...v0.12.6)

---

### [v0.12.5](https://github.com/decodelabs/veneer/commits/v0.12.5) - 18th February 2025

- Fixed instance access in ClassGenerator
- Ensure plugins are scanned regardless of mount status

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.12.4...v0.12.5)

---

### [v0.12.4](https://github.com/decodelabs/veneer/commits/v0.12.4) - 18th February 2025

- Control mounting when fetching bindings

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.12.3...v0.12.4)

---

### [v0.12.3](https://github.com/decodelabs/veneer/commits/v0.12.3) - 14th February 2025

- Added Stringable to Wrapper
- Removed Pandora dev dependency
- Improved Exception syntax
- Updated dependencies

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.12.2...v0.12.3)

---

### [v0.12.2](https://github.com/decodelabs/veneer/commits/v0.12.2) - 12th February 2025

- Removed Coercion dependency

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.12.1...v0.12.2)

---

### [v0.12.1](https://github.com/decodelabs/veneer/commits/v0.12.1) - 12th February 2025

- Fixed plugin binding for non-lazy providers

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.12.0...v0.12.1)

---

### [v0.12.0](https://github.com/decodelabs/veneer/commits/v0.12.0) - 12th February 2025

- Simplified binding structure
- Made Veneer manager a proxy
- Use ghosts and proxies for lazy loading
- Removed SelfLoader interface
- Removed LazyLoad attribute
- Added support for property hooks
- Added public keyword to binding consts
- Added support for never-returning methods in Stubs
- Upgraded PHPStan to v2
- Added @phpstan-require-implements constraints
- Added Proxy analyze test
- Added PHP8.4 to CI workflow
- Made PHP8.4 minimum version

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.11.6...v0.12.0)

---

### [v0.11.6](https://github.com/decodelabs/veneer/commits/v0.11.6) - 21st August 2024

- Made class constants PascalCase

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.11.5...v0.11.6)

---

### [v0.11.5](https://github.com/decodelabs/veneer/commits/v0.11.5) - 9th August 2024

- Improved context checking in stub generator

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.11.4...v0.11.5)

---

### [v0.11.4](https://github.com/decodelabs/veneer/commits/v0.11.4) - 9th August 2024

- Ignore bootstraps in stub generator

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.11.3...v0.11.4)

---

### [v0.11.3](https://github.com/decodelabs/veneer/commits/v0.11.3) - 9th August 2024

- Improved stub generator

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.11.2...v0.11.3)

---

### [v0.11.2](https://github.com/decodelabs/veneer/commits/v0.11.2) - 31st July 2024

- Made all bindings lazy by default

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.11.1...v0.11.2)

---

### [v0.11.1](https://github.com/decodelabs/veneer/commits/v0.11.1) - 17th July 2024

- Improved non-Slingshot object instantiation

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.11.0...v0.11.1)

---

### [v0.11.0](https://github.com/decodelabs/veneer/commits/v0.11.0) - 17th July 2024

- Made Slingshot optional

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.25...v0.11.0)

---

### [v0.10.25](https://github.com/decodelabs/veneer/commits/v0.10.25) - 29th April 2024

- Fixed re-adding early-bound instances to container

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.24...v0.10.25)

---

### [v0.10.24](https://github.com/decodelabs/veneer/commits/v0.10.24) - 14th November 2023

- Fixed Slingshot dependency

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.23...v0.10.24)

---

### [v0.10.23](https://github.com/decodelabs/veneer/commits/v0.10.23) - 10th November 2023

- Switched to Slingshot for function invocation
- Moved to PHP8.1 minimum

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.22...v0.10.23)

---

### [v0.10.22](https://github.com/decodelabs/veneer/commits/v0.10.22) - 9th November 2023

- Fixed handling of private and protected class constants

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.21...v0.10.22)

---

### [v0.10.21](https://github.com/decodelabs/veneer/commits/v0.10.21) - 8th November 2023

- Improved deferred binding argument handling

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.20...v0.10.21)

---

### [v0.10.20](https://github.com/decodelabs/veneer/commits/v0.10.20) - 7th November 2023

- Added Container resolution for deferred constructor arguments

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.19...v0.10.20)

---

### [v0.10.19](https://github.com/decodelabs/veneer/commits/v0.10.19) - 5th October 2023

- Added ArrayAccess support to Plugin Wrapper
- Added IteratorAggregate support to Plugin Wrapper

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.18...v0.10.19)

---

### [v0.10.18](https://github.com/decodelabs/veneer/commits/v0.10.18) - 26th September 2023

- Converted phpstan doc comments to generic

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.17...v0.10.18)

---

### [v0.10.17](https://github.com/decodelabs/veneer/commits/v0.10.17) - 27th January 2023

- Added slashes to stub class name strings

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.16...v0.10.17)

---

### [v0.10.16](https://github.com/decodelabs/veneer/commits/v0.10.16) - 28th November 2022

- Ensure protected constructors are invocable in bindings

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.15...v0.10.16)

---

### [v0.10.15](https://github.com/decodelabs/veneer/commits/v0.10.15) - 28th November 2022

- Added replacePlugin helper
- Simplified bin dir handling

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.14...v0.10.15)

---

### [v0.10.14](https://github.com/decodelabs/veneer/commits/v0.10.14) - 22nd November 2022

- Properly fixed binding ref generation

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.13...v0.10.14)

---

### [v0.10.13](https://github.com/decodelabs/veneer/commits/v0.10.13) - 22nd November 2022

- Fixed binding ref generation

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.12...v0.10.13)

---

### [v0.10.12](https://github.com/decodelabs/veneer/commits/v0.10.12) - 22nd November 2022

- Fixed use alias mapping for multiple refs
- Migrated to use effigy in CI workflow
- Fixed PHP8.1 testing

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.11...v0.10.12)

---

### [v0.10.11](https://github.com/decodelabs/veneer/commits/v0.10.11) - 29th September 2022

- Turned off strict mode in PuginWrapper

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.10...v0.10.11)

---

### [v0.10.10](https://github.com/decodelabs/veneer/commits/v0.10.10) - 29th September 2022

- Fixed stub generator

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.9...v0.10.10)

---

### [v0.10.9](https://github.com/decodelabs/veneer/commits/v0.10.9) - 29th September 2022

- Auto-wrap plugins if type accepted
- Removed Atlas and Terminus dependencies for stubs

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.8...v0.10.9)

---

### [v0.10.8](https://github.com/decodelabs/veneer/commits/v0.10.8) - 28th September 2022

- Added ensurePlugin() helper for lazy loaders

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.7...v0.10.8)

---

### [v0.10.7](https://github.com/decodelabs/veneer/commits/v0.10.7) - 27th September 2022

- Fixed static type ref handling

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.6...v0.10.7)

---

### [v0.10.6](https://github.com/decodelabs/veneer/commits/v0.10.6) - 27th September 2022

- Fixed nullable type export
- Updated composer check script

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.5...v0.10.6)

---

### [v0.10.5](https://github.com/decodelabs/veneer/commits/v0.10.5) - 27th September 2022

- Fixed plugin wrapper type inference

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.4...v0.10.5)

---

### [v0.10.4](https://github.com/decodelabs/veneer/commits/v0.10.4) - 27th September 2022

- Improved stub generation

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.3...v0.10.4)

---

### [v0.10.3](https://github.com/decodelabs/veneer/commits/v0.10.3) - 27th September 2022

- Export full method defs in Stubs

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.2...v0.10.3)

---

### [v0.10.2](https://github.com/decodelabs/veneer/commits/v0.10.2) - 27th September 2022

- Fixed stub generator scanner

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.1...v0.10.2)

---

### [v0.10.1](https://github.com/decodelabs/veneer/commits/v0.10.1) - 27th September 2022

- Improved stub generator

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.10.0...v0.10.1)

---

### [v0.10.0](https://github.com/decodelabs/veneer/commits/v0.10.0) - 27th September 2022

- Converted Plugin handling to use Attributes
- Enabled direct loading of Plugins
- Removed trailing space from stubs
- Updated CI environment

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.9.2...v0.10.0)

---

### [v0.9.2](https://github.com/decodelabs/veneer/commits/v0.9.2) - 23rd August 2022

- Added concrete types to all members

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.9.1...v0.9.2)

---

### [v0.9.1](https://github.com/decodelabs/veneer/commits/v0.9.1) - 23rd August 2022

- Updated Stub Generator testing

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.9.0...v0.9.1)

---

### [v0.9.0](https://github.com/decodelabs/veneer/commits/v0.9.0) - 22nd August 2022

- Removed PHP7 compatibility
- Moved Stub Generator out of PHPStan scope
- Updated PSR Container interface to v2
- Updated ECS to v11
- Updated PHPUnit to v9

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.8.5...v0.9.0)

---

### [v0.8.5](https://github.com/decodelabs/veneer/commits/v0.8.5) - 25th March 2022

- Allow disabling deferrals for PHPStan

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.8.4...v0.8.5)

---

### [v0.8.4](https://github.com/decodelabs/veneer/commits/v0.8.4) - 24th March 2022

- Defer target construction until after binding

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.8.3...v0.8.4)

---

### [v0.8.3](https://github.com/decodelabs/veneer/commits/v0.8.3) - 24th March 2022

- Added lazy loading support

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.8.2...v0.8.3)

---

### [v0.8.2](https://github.com/decodelabs/veneer/commits/v0.8.2) - 9th March 2022

- Transitioned from Travis to GHA
- Updated PHPStan and ECS dependencies

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.8.1...v0.8.2)

---

### [v0.8.1](https://github.com/decodelabs/veneer/commits/v0.8.1) - 20th October 2021

- Fixed stub generator

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.8.0...v0.8.1)

---

### [v0.8.0](https://github.com/decodelabs/veneer/commits/v0.8.0) - 20th October 2021

- Simplified binding structure

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.7.5...v0.8.0)

---

### [v0.7.5](https://github.com/decodelabs/veneer/commits/v0.7.5) - 11th May 2021

- Added stub generator system

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.7.3...v0.7.5)

---

### [v0.7.3](https://github.com/decodelabs/veneer/commits/v0.7.3) - 5th May 2021

- Disabled strict types for ProxyTrait __callStatic

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.7.2...v0.7.3)

---

### [v0.7.2](https://github.com/decodelabs/veneer/commits/v0.7.2) - 16th April 2021

- Disabled strict typing in Binding class

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.7.1...v0.7.2)

---

### [v0.7.1](https://github.com/decodelabs/veneer/commits/v0.7.1) - 7th April 2021

- Updated for max PHPStan conformance

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.7.0...v0.7.1)

---

### [v0.7.0](https://github.com/decodelabs/veneer/commits/v0.7.0) - 18th March 2021

- Enabled PHP8 testing

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.6.4...v0.7.0)

---

### [v0.6.4](https://github.com/decodelabs/veneer/commits/v0.6.4) - 6th October 2020

- Added PSR11 container accessors
- Applied full PSR12 standards
- Added PSR12 check to Travis build

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.6.3...v0.6.4)

---

### [v0.6.3](https://github.com/decodelabs/veneer/commits/v0.6.3) - 5th October 2020

- Improved readme
- Updated PHPStan

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.6.2...v0.6.3)

---

### [v0.6.2](https://github.com/decodelabs/veneer/commits/v0.6.2) - 4th October 2020

- Updated binding handling for PHPStan

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.6.1...v0.6.2)

---

### [v0.6.1](https://github.com/decodelabs/veneer/commits/v0.6.1) - 4th October 2020

- Fixed cached binding support for PHPStan

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.6.0...v0.6.1)

---

### [v0.6.0](https://github.com/decodelabs/veneer/commits/v0.6.0) - 4th October 2020

- Restructured main library classes
- Simplified proxy registration
- Removed Veneer facade
- Removed support for auto namespace loading
- Removed listener structure
- Normalised plugin interface
- Added binding cache support for PHPStan

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.5.5...v0.6.0)

---

### [v0.5.5](https://github.com/decodelabs/veneer/commits/v0.5.5) - 2nd October 2020

- Updated glitch-support

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.5.4...v0.5.5)

---

### [v0.5.4](https://github.com/decodelabs/veneer/commits/v0.5.4) - 2nd October 2020

- Removed Glitch dependency

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.5.3...v0.5.4)

---

### [v0.5.3](https://github.com/decodelabs/veneer/commits/v0.5.3) - 30th September 2020

- Switched to Exceptional for exception generation

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.5.2...v0.5.3)

---

### [v0.5.2](https://github.com/decodelabs/veneer/commits/v0.5.2) - 25th September 2020

- Switched to Glitch Dumpable interface

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.5.1...v0.5.2)

---

### [v0.5.1](https://github.com/decodelabs/veneer/commits/v0.5.1) - 24th September 2020

- Updated Composer dependency handling

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.5.0...v0.5.1)

---

### [v0.5.0](https://github.com/decodelabs/veneer/commits/v0.5.0) - 26th October 2019

- Added namespace white / black list
- Added facade instance getter
- Added shared Veneer context facade
- Removed class_exists workaround
- Improved PHPStan setup

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.4.1...v0.5.0)

---

### [v0.4.1](https://github.com/decodelabs/veneer/commits/v0.4.1) - 15th October 2019

- Updated binding dump handling
- Added PHPStan support
- Bugfixes from PHPStan max scan

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.4.0...v0.4.1)

---

### [v0.4.0](https://github.com/decodelabs/veneer/commits/v0.4.0) - 26th September 2019

- Fixed multiple plugin binding namespacing
- Added dump handling to plugins
- Added manual class alias loader on binding instantiation

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.3.0...v0.4.0)

---

### [v0.3.0](https://github.com/decodelabs/veneer/commits/v0.3.0) - 24th September 2019

- Moved autoload code to Listener structure
- Created singleton Register for default Listener
- Lazy load bindings

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.2.0...v0.3.0)

---

### [v0.2.0](https://github.com/decodelabs/veneer/commits/v0.2.0) - 13th September 2019

- Normalised interfaces and traits
- Added Facade plugin binding mechanism

[Full list of changes](https://github.com/decodelabs/veneer/compare/v0.1.0...v0.2.0)

---

### [v0.1.0](https://github.com/decodelabs/veneer/commits/v0.1.0)

- Created initial Facade generator structure

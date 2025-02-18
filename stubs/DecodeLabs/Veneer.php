<?php
/**
 * This is a stub file for IDE compatibility only.
 * It should not be included in your projects.
 */
namespace DecodeLabs;

use DecodeLabs\Veneer\Proxy as Proxy;
use DecodeLabs\Veneer\ProxyTrait as ProxyTrait;
use DecodeLabs\Veneer\Manager as Inst;
use Psr\Container\ContainerInterface as Ref0;
use DecodeLabs\Veneer\Binding as Ref1;
use DecodeLabs\Veneer\Stub\Generator as Ref2;

class Veneer implements Proxy
{
    use ProxyTrait;

    public const Veneer = 'DecodeLabs\\Veneer';
    public const VeneerTarget = Inst::class;

    protected static Inst $_veneerInstance;

    public static function setContainer(Ref0 $container): void {}
    public static function getGlobalManager(): Inst {
        return static::$_veneerInstance->getGlobalManager();
    }
    public static function register(string $providerClass, string $proxyClass): bool {
        return static::$_veneerInstance->register(...func_get_args());
    }
    public static function has(string $proxyClass): bool {
        return static::$_veneerInstance->has(...func_get_args());
    }
    public static function replacePlugin(object $instance, string $name, mixed $plugin): void {}
    public static function getBindings(bool $mount = false): array {
        return static::$_veneerInstance->getBindings(...func_get_args());
    }
    public static function getBinding(string $name, bool $mount = false): ?Ref1 {
        return static::$_veneerInstance->getBinding(...func_get_args());
    }
    public static function newStubGenerator(string $scanDir, string $stubDir): Ref2 {
        return static::$_veneerInstance->newStubGenerator(...func_get_args());
    }
};

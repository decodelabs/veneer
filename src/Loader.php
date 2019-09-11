<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

interface Loader
{
    public function isRegistered(): bool;
    public function register(): Loader;
    public function unregister(): Loader;
}

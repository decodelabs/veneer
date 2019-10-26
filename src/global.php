<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);

/**
 * global helpers
 */
namespace DecodeLabs\Veneer
{
    use DecodeLabs\Veneer as Facade;
    use DecodeLabs\Veneer\Register;
    use DecodeLabs\Veneer\Context;

    Register::getGlobalListener();
    Context::registerFacade(Facade::class);
}

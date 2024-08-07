<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer;

use Attribute;

#[Attribute(
    Attribute::TARGET_CLASS |
    Attribute::TARGET_PROPERTY
)]
class EagerLoad
{
}

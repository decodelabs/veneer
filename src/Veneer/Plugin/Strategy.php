<?php

/**
 * @package Veneer
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Veneer\Plugin;

enum Strategy
{
    case Manual;
    case Auto;
    case Lazy;

    public function isManual(): bool {
        return $this === self::Manual;
    }

    public function isEagerAuto(): bool {
        return $this === self::Auto;
    }

    public function isAuto(): bool {
        return
            $this === self::Auto ||
            $this === self::Lazy;
    }

    public function isLazy(): bool {
        return $this === self::Lazy;
    }
}

<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer\Listener;

use DecodeLabs\Veneer\Listener;
use DecodeLabs\Veneer\ListenerTrait;
use DecodeLabs\Veneer\Manager\Aliasing;

use DecodeLabs\Glitch\Stack\Frame;

use Psr\Container\ContainerInterface;

class Autoload implements Listener
{
    use ListenerTrait;

    private static $default;
    private $callback;

    /**
     * Get default global listener
     */
    public static function getDefaultInstance(): Listener
    {
        if (!self::$default) {
            self::$default = new self();
        }

        return self::$default;
    }

    /**
     * Register loader listener
     */
    public function startListening(): void
    {
        $this->stopListening();

        spl_autoload_register($this->callback = function (string $class): void {
            $parts = explode('\\', $class);
            $name = array_pop($parts);
            $namespace = empty($parts) ? null : implode('\\', $parts);

            if ($namespace !== null) {
                $frame = Frame::create(2);

                if (
                    $frame->getType() == 'globalFunction' &&
                    in_array($frame->getFunctionName(), ['class_exists', 'interface_exists', 'trait_exists']) &&
                    !defined('__PHPSTAN_RUNNING__')
                ) {
                    return;
                }
            }

            foreach ($this->managers as $manager) {
                if ($manager->load($name, $namespace)) {
                    return;
                }
            }
        });
    }

    /**
     * Unregister listener
     */
    public function stopListening(): void
    {
        if (!$this->callback) {
            return;
        }

        spl_autoload_unregister($this->callback);
        $this->callback = null;
    }

    /**
     * Is listener listening?
     */
    public function isListening(): bool
    {
        return $this->callback !== null;
    }
}

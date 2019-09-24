<?php
/**
 * This file is part of the Veneer package
 * @license http://opensource.org/licenses/MIT
 */
declare(strict_types=1);
namespace DecodeLabs\Veneer;

use DecodeLabs\Veneer\Manager\Aliasing;
use Psr\Container\ContainerInterface;

trait ListenerTrait
{
    protected $managers = [];

    /**
     * Register manager instance
     */
    public function registerManager(Manager $manager): void
    {
        $id = spl_object_id($manager);
        $this->managers[$id] = $manager;
    }

    /**
     * Unregister manager instance
     */
    public function unregisterManager(Manager $manager): void
    {
        $id = spl_object_id($manager);
        unset($this->managers[$id]);
    }

    /**
     * Is manager registered
     */
    public function hasManager(Manager $manager): bool
    {
        $id = spl_object_id($manager);
        return isset($this->managers[$id]);
    }

    /**
     * Get list of registered managers
     */
    public function getManagers(): array
    {
        return $this->managers;
    }

    /**
     * Get default manager
     */
    public function getDefaultManager(): Manager
    {
        if (empty($this->managers)) {
            $manager = new Aliasing();
            $this->registerManager($manager);
            return $manager;
        } else {
            foreach ($this->managers as $manager) {
                return $manager;
            }
        }
    }
}

<?php
/**
 * Autoload Interface
 *
 * @package Service\Autoload
 */
namespace Evoke\Service\Autoload;

/**
 * Autoload Interface
 *
 * @author    Paul Young <evoke@youngish.org>
 * @copyright Copyright (c) 2015 Paul Young
 * @license   MIT
 * @package   Service\Autoload
 */
interface AutoloadIface
{
    /**
     * Autoload the specified class.
     *
     * @param string $name The fully namespaced class to load.
     */
    public function load($name);
}
// EOF

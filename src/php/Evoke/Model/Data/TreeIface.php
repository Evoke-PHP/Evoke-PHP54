<?php
/**
 * TreeIface
 *
 * @package Model\Data
 */
namespace Evoke\Model\Data;

/**
 * TreeIface
 *
 * @author    Paul Young <evoke@youngish.homelinux.org>
 * @copyright Copyright (c) 2013 Paul Young
 * @license   MIT
 * @package   Model\Data
 */
interface TreeIface extends \RecursiveIterator
{
	/**
	 * Add a node to the tree.
	 *
	 * @param TreeIface The node to add as a child.
	 */
	public function add(TreeIface $node);

	/**
	 * Get the value of the current node.
	 *
	 * @return mixed The value of the current node.
	 */
	public function get();   
	
	/**
	 * Set the value of the node.
	 *
	 * @param mixed Value for the node.
	 */
	public function set($value);
}
// EOF
<?php
namespace Evoke\Persistence\DB\Table;

/**
 * ListIDIface
 *
 * @author Paul Young <evoke@youngish.homelinux.org>
 * @copyright Copyright (c) 2012 Paul Young
 * @license MIT
 * @package Persistence
 */
interface ListIDIface
{
	/**
	 * Get a new List ID from the List_IDs table.
	 *
	 * @param string The table name to get the List_ID for.
	 * @param string The table field to get the List_ID for.
	 * @return mixed The new List_ID value or an exception is raised.
	 */
	public function getNew($table, $field);
}
// EOF
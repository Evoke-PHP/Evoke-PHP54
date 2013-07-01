<?php
/**
 * HTTP Media Type Router Interface
 *
 * @package Network\HTTP\MediaType
 */
namespace Evoke\Network\HTTP\MediaType;

use Rule\RuleIface;

/**
 * HTTP Media Type Router Interface
 *
 * @author Paul Young <evoke@youngish.homelinux.org>
 * @copyright Copyright (c) 2012 Paul Young
 * @license MIT
 * @package Network\HTTP\MediaType
 */
interface RouterIface
{
	/**
	 * Add a rule to the router.
	 *
	 * @param RuleIface The rule to add to the router.
	 */
	public function addRule(RuleIface $rule);

	/**
	 * Perform the routing based on the rules.
	 */
	public function route();
}
// EOF